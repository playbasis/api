require('newrelic');
/**
 * Module dependencies.
 */

var express = require('express')
  , routes = require('./routes')
  , user = require('./routes/user')
  , http = require('https')
  , path = require('path')
  , fs = require('fs')
  , io = require('socket.io')
  , mongoose = require('mongoose')
  , instagram = require('instagram-node-lib');

var app = express();

// all environments
app.set('port', process.env.PORT || 3003);
app.set('views', __dirname + '/views');
app.set('view engine', 'jade');
app.use(express.favicon());
app.use(express.logger('dev'));
app.use(express.bodyParser());
app.use(express.methodOverride());
app.use(app.router);
app.use(express.static(path.join(__dirname, 'public')));

// development only
if ('development' == app.get('env')) {
	app.use(express.errorHandler());
}

app.get('/', routes.index);
app.get('/users', user.list);

//connect to mongodb
var dbReady = false;
var mongoose = require('mongoose');

function requiredEnv(name) {
    if (!process.env[name]) {
        throw new Error('Missing required environment variable: ' + name);
    }
    return process.env[name];
}

function mongoPort() {
    var port = parseInt(requiredEnv('MONGO_HOST_PORT'), 10);
    if (isNaN(port)) {
        throw new Error('MONGO_HOST_PORT must be a number');
    }
    return port;
}

function mongoConnectionOptions() {
    return {
        user: requiredEnv('MONGO_HOST_USERNAME'),
        pass: requiredEnv('MONGO_HOST_PASSWORD'),
        auth: { authSource: process.env['MONGO_HOST_AUTH_SOURCE'] || 'admin' }
    };
}

var IGFeed;
var IGMeta;
var metas = {}
db = mongoose.createConnection(requiredEnv('MONGO_HOST_ADDR'), 'admin', mongoPort(), mongoConnectionOptions());
db.on('error', console.error.bind(console, 'connection error:'));
db.once('open', function callback(){
	
	var feedSchema = mongoose.Schema({
		user: 'string',
		name: 'string',
		id: 'string',
		profile_image: 'string',
		photo: 'string',
		thumbnail: 'string',
		caption: 'string',
		tag: { type: 'string', index: true },
		time: 'number',
        created_time: {type: Date, default: Date.now}
	});
	IGFeed = db.model('IGFeed', feedSchema);

	var metaSchema = mongoose.Schema({
		tag_name: 'string',
		last_feed_time: 'number'
	});
	IGMeta = db.model('IGMeta', metaSchema);

	dbReady = true;
	console.log('db connected!');
});
console.log('connecting to db...');

var options = {
	key:  fs.readFileSync('/usr/bin/ssl/pbapp.net.key'),
	cert: fs.readFileSync('/usr/bin/ssl/pbapp.net.crt'),
	ca:   fs.readFileSync('/usr/bin/ssl/gd_bundle.crt'),
	requestCert: true,
	rejectUnauthorized: false
};

var server = http.createServer(options, app);
io = io.listen(server);
server.listen(app.get('port'), function(){
	console.log('Express server listening on port ' + app.get('port'));
});

function requiredSocialEnv(name) {
	if (!process.env[name]) {
		throw new Error('Missing required environment variable: ' + name);
	}
	return process.env[name];
}

instagram.set('client_id', requiredSocialEnv('INSTAGRAM_CLIENT_ID'));
instagram.set('client_secret', requiredSocialEnv('INSTAGRAM_CLIENT_SECRET'));
instagram.set('callback_url', 'https://instagram.pbapp.net/feed');

var BASIC_AUTH_USER = requiredSocialEnv('INSTAGRAM_BASIC_AUTH_USER');
var BASIC_AUTH_PASSWORD = requiredSocialEnv('INSTAGRAM_BASIC_AUTH_PASSWORD');

io.sockets.on('connection', function(socket){
	var dateObj = new Date();
	socket.emit('newigpost', {'time': dateObj.getTime()});
});

app.get('/hello', function(req, res){
	res.send('Hello World');
});

var auth = express.basicAuth(function(user, pass){
	return user === BASIC_AUTH_USER && pass === BASIC_AUTH_PASSWORD;
});

app.get('/subscribe/tag/:tag', auth, function(req, res)
{
	var tag = req.params.tag;
	IGMeta.where('tag_name').equals(tag).count(function(err, count){
	 	if (err){
	 		console.log(err);
	 		return;
	 	}
	 	if(count > 0)
	 		return;
	 	var dateObj = new Date();
		var meta = new IGMeta({
			'tag_name': tag,
			'last_feed_time': Math.round(dateObj.getTime() / 1000)
		});
		metas[tag] = { last_feed_time: meta.last_feed_time };
		meta.save(function(err){
			if(err){
				console.log(err);
			}
		});
	});
	instagram.subscriptions.subscribe({ object : 'tag', object_id : tag });
	res.send(200);
});

app.get('/unsubscribe/tag/:tag', auth, function(req, res){
	var tag = req.params.tag;
	instagram.subscriptions.list({ complete: function(data, pagination){
		console.log('unsubscribe from: ' + tag);
		var datalen = data.length;
		for(var i=0; i<datalen; ++i){
			if(data[i].object_id != tag)
				continue;
			instagram.subscriptions.unsubscribe({ id: data[i].id });
			break;
		}
	}});
	res.send(200);
});

app.get('/unsubscribe/all', auth, function(req, res){
    instagram.subscriptions.list({ complete: function(data, pagination){
        console.log('unsubscribe all');
        var datalen = data.length;
        for(var i=0; i<datalen; ++i){
            instagram.subscriptions.unsubscribe({ id: data[i].id });
        }
    }});
    res.send(200);
});

//#TODO first start of app should run this path
app.get('/subscription', function(req, res){
	instagram.subscriptions.list();
	res.send(200);
});

app.get('/feed', function(req, res){
	console.log(req.query);
	if(req.query['hub.mode'] == 'subscribe'){
		res.send(req.query['hub.challenge']);
		return;
	}
	res.send(200);
});

function processRecentFeeds(tag)
{
	instagram.tags.recent({ name: tag, complete: function(data, pagination){
		var datalen = data.length;
		console.log('recents: ' + datalen);
		var maxFeedTime = metas[tag].last_feed_time;
		console.log('last feed time: ' + maxFeedTime);
		for(var i=0; i<datalen; ++i){
			var feed = data[i];
			//ignore old feeds
			var feedTime = feed.created_time;
			if(feedTime <= metas[tag].last_feed_time)
				continue;
			if(feedTime > maxFeedTime)
				maxFeedTime = feedTime;

			//save new feed
			console.log(feed.created_time);
			console.log(feed.user.username);
			
			var entry = new IGFeed({
				'user': feed.user.username,
				'name': feed.user.full_name,
				'id': feed.user.id,
				'profile_image': feed.user.profile_picture,
				'photo': feed.images.standard_resolution.url,
				'thumbnail' : feed.images.thumbnail.url,
				'caption': (feed.caption) ? feed.caption.text : '',
				'tag': tag,
				'time': feed.created_time
			});
			console.log('saving entry: ' + feedTime);
			entry.save(function(err){
				if(err){
					console.log(err);
					return;
				}
				console.log('post saved!');
				var dateObj = new Date();
				//tell clients to update data
				io.sockets.emit('newigpost', {'time': dateObj.getTime()});
			});
		}
		if(metas[tag].last_feed_time < maxFeedTime){
			console.log('update feed time: ' + tag + ' - ' + maxFeedTime);
			metas[tag].last_feed_time = maxFeedTime;
			IGMeta.findOneAndUpdate({ tag_name: tag }, { last_feed_time: maxFeedTime }, function(err){
				if(err){
					console.log(err);
				}
			});
		}
	}});
}

app.post('/feed', function(req, res)
{
	console.log('---------- ig post ----------');
	//console.log(req.body);

	//save data to mongodb
	if(!dbReady){
		res.send(200);
		return;
	}
	var body = req.body;
	var bodylen = body.length;
	console.log('update: ' + bodylen);
	for(var i=0; i<bodylen; ++i){
		var tag = body[i].object_id;
		console.log('tag: ' + tag);
		if(!metas.hasOwnProperty(tag)){
			console.log('finding tag: ' + tag);
			IGMeta.findOne({ tag_name: tag }, function(err, meta){
				if(meta){
					metas[meta.tag_name] = { last_feed_time: meta.last_feed_time };
					console.log('found tag in meta: ' + meta.tag_name + ' last feed time: ' + metas[meta.tag_name].last_feed_time);
					processRecentFeeds(meta.tag_name);
				}
				else console.log('not found tag in meta: ' + tag.tag_name);
			});
			continue;
		}
		console.log('start query recents: ' + tag);
		processRecentFeeds(tag);
	}
	res.send(200);
});

/* memory leak detection */

var memwatch = require('memwatch');

// 'leak' event
memwatch.on('leak', function(info) {
    console.log(info);
});

// after 'gc' event, this should be baselnie
memwatch.on('stats', function(stats) {
    console.log(stats);
});
