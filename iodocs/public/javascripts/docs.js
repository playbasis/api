(function() {

    // Storing common selections
    var allEndpoints = $('li.endpoint'),
        allEndpointsLength = allEndpoints.length,
        allMethodLists = $('ul.methods'),
        allMethodListsLength = allMethodLists.length;

    $(allEndpoints[0]).addClass('active');

    window.addEventListener("keydown", function(e) {
        // space and arrow keys
        if([27, 32, 37, 38, 39, 40].indexOf(e.keyCode) > -1) {
            if(e.keyCode === 27){
                var $focused = $(':focus');
                $focused.blur();
            }
            if(e.keyCode === 32){
                if( $('input:focus').length == 0) {
                    e.preventDefault();
                    var endpoint_index = $("li.endpoint.active").index();
                    if(endpoint_index != -1){
                        $("li.endpoint.active").toggleClass('expanded');
                        $(allEndpoints[endpoint_index].lastChild).toggle();
                    }
                    else {
                        $("ul.methods li.active").parent().parent().toggleClass('active');
                        $("ul.methods li.active").parent().parent().toggleClass('expanded');
                        $("ul.methods li.active").parent().toggle();
                        $("ul.methods li.active").toggleClass('active');
                    }
                }
            }
            if(e.keyCode === 37){
                var endpoint_index = $("li.endpoint.active").index();
                if($('input:focus').length == 0) {
                    if (endpoint_index != -1) {
                        $("li.endpoint.active").removeClass('expanded');
                        $(allEndpoints[endpoint_index].lastChild).hide();
                    }
                    else {
                        var method_index = $("ul.methods li.active").index();
                        var methods = $("ul.methods li.active").parent().children();
                        $(methods[method_index].lastChild).hide();
                    }
                }
            }


            if(e.keyCode === 38){
                var endpoint_index = $("li.endpoint.active").index();
                if($('input:focus').length == 0) {
                    if(endpoint_index != -1){
                        if(endpoint_index != 0) {
                            if( $(allEndpoints[endpoint_index-1]).hasClass('expanded')){
                                $(allEndpoints[endpoint_index-1].lastChild.lastChild).addClass('active');
                                $(allEndpoints[endpoint_index]).removeClass('active');
                            } else {
                                $(allEndpoints[endpoint_index-1]).addClass('active');
                                $(allEndpoints[endpoint_index]).removeClass('active');
                            }
                        }
                    } else {
                        var method_index = $("ul.methods li.active").index();
                        var methods = $("ul.methods li.active").parent().children();

                        if(method_index != 0){
                            $(methods[method_index-1]).addClass('active');
                            $(methods[method_index]).removeClass('active');
                        } else {
                            $(methods[method_index]).parent().parent().addClass('active');
                            $(methods[method_index]).removeClass('active');
                        }
                    }
                }
            }
            if(e.keyCode === 39){
                var endpoint_index = $("li.endpoint.active").index();
                if($('input:focus').length == 0) {
                    if (endpoint_index != -1) {
                        $("li.endpoint.active").addClass('expanded');
                        $(allEndpoints[endpoint_index].lastChild).show();
                    }
                    else {
                        var method_index = $("ul.methods li.active").index();
                        var method_lengh = $("ul.methods li.active").parent().children().length;
                        var methods = $("ul.methods li.active").parent().children();
                        var methods_table = $(methods[method_index]).children().children().children().children();
                        var methods_table2 = $(methods_table[1]).children();
                        $(methods[method_index].lastChild).show();
                        $(methods_table2[1].firstChild).focus();
                    }
                }
            }
            if(e.keyCode === 40){
                var endpoint_index = $("li.endpoint.active").index();
                if($('input:focus').length == 0) {
                    if (endpoint_index != -1) {
                        if (endpoint_index != allEndpointsLength - 1) {
                            if ($(allEndpoints[endpoint_index]).hasClass('expanded')) {
                                $(allEndpoints[endpoint_index].lastChild.firstChild).addClass('active');
                                $(allEndpoints[endpoint_index]).removeClass('active');
                            } else {
                                $(allEndpoints[endpoint_index + 1]).addClass('active');
                                $(allEndpoints[endpoint_index]).removeClass('active');
                            }
                        }
                    } else {
                        var method_index = $("ul.methods li.active").index();
                        var method_lengh = $("ul.methods li.active").parent().children().length;
                        var methods = $("ul.methods li.active").parent().children();

                        if (method_index != method_lengh - 1) {
                            $(methods[method_index + 1]).addClass('active');
                            $(methods[method_index]).removeClass('active');
                        } else {
                            var parent_index = $(methods[method_index]).parent().parent().index();
                            $(allEndpoints[parent_index + 1]).addClass('active');
                            $(methods[method_index]).removeClass('active');
                        }
                    }
                }
            }
        }
    }, false);

    function listMethods(context) {
        var methodsList = $('ul.methods', context || null);

        for (var i = 0, len = methodsList.length; i < len; i++) {
            $(methodsList[i]).slideDown();
        }
    }

    // Toggle show/hide of method details, form, and results
    $('li.method > div.title').click(function() {
        $('form', this.parentNode).slideToggle();
        var $actived = $('.active');
        $actived.toggleClass('active');
        $(this.parentNode).toggleClass('active');
    })

    // Toggle an endpoint
    $('li.endpoint > h3.title span.name').click(function() {
        $('ul.methods', this.parentNode.parentNode).slideToggle();
        $(this.parentNode.parentNode).toggleClass('expanded');
        var $actived = $('.active');
        $actived.toggleClass('active');
        $(this.parentNode.parentNode).toggleClass('active');
    })

    // Toggle all endpoints
    $('#toggle-endpoints').click(function(event) {
        event.preventDefault();

        // Check for collapsed endpoints (hidden methods)
        var endpoints = $('ul.methods:not(:visible)'),
            endpointsLength = endpoints.length;

        if (endpointsLength > 0) {
            // Some endpoints are collapsed, expand them.
            for (var x = 0; x < endpointsLength; x++) {
                var methodsList = $(endpoints[x]);
                methodsList.slideDown();
                methodsList.parent().toggleClass('expanded', true)

            }
        } else {
            // All endpoints are expanded, collapse them
            var endpoints = $('ul.methods'),
                endpointsLength = endpoints.length;

            for (var x = 0; x < endpointsLength; x++) {
                var methodsList = $(endpoints[x]);
                methodsList.slideUp();
                methodsList.parent().toggleClass('expanded', false)
                var $actived = $('.active');
                $actived.toggleClass('active');
                $(allEndpoints[0]).toggleClass('active');
            }
        }

    })

    // Toggle all methods
    $('#toggle-methods').click(function(event) {
        event.preventDefault();

        var methodForms = $('ul.methods form:not(:visible)'), // Any hidden method forms
            methodFormsLength = methodForms.length;

        // Check if any method is not visible. If so, expand all methods.
        if (methodFormsLength > 0) {
            var methodLists = $('ul.methods:not(:visible)'), // Any hidden methods
            methodListsLength = methodLists.length;

            // First make sure all the hidden endpoints are expanded.
            for (var x = 0; x < methodListsLength; x++) {
                $(methodLists[x]).slideDown();
            }

            // Now make sure all the hidden methods are expanded.
            for (var y = 0; y < methodFormsLength; y++) {
                $(methodForms[y]).slideDown();
            }

        } else {
            // Hide all visible method forms
            var visibleMethodForms = $('ul.methods form:visible'),
                visibleMethodFormsLength = visibleMethodForms.length;

            for (var i = 0; i < visibleMethodFormsLength; i++) {
                $(visibleMethodForms[i]).slideUp();
            }
        }

        for (var z = 0; z < allEndpointsLength; z++) {
            $(allEndpoints[z]).toggleClass('expanded', true);
        }
    })

    // List methods for a particular endpoint.
    // Hide all forms if visible
    $('li.list-methods a').click(function(event) {
        event.preventDefault();

        // Make sure endpoint is expanded
        var endpoint = $(this).closest('li.endpoint'),
            methods = $('li.method form', endpoint);

        listMethods(endpoint);

        // Make sure all method forms are collapsed
        var visibleMethods = $.grep(methods, function(method) {
            return $(method).is(':visible')
        })

        $(visibleMethods).each(function(i, method) {
            $(method).slideUp();
        })

        $(endpoint).toggleClass('expanded', true);
        var $actived = $('.active');
        $actived.toggleClass('active');
        $(endpoint).toggleClass('active');

    })

    // Expand methods for a particular endpoint.
    // Show all forms and list all methods
    $('li.expand-methods a').click(function(event) {
        event.preventDefault();

        // Make sure endpoint is expanded
        var endpoint = $(this).closest('li.endpoint'),
            methods = $('li.method form', endpoint);

        listMethods(endpoint);

        // Make sure all method forms are expanded
        var hiddenMethods = $.grep(methods, function(method) {
            return $(method).not(':visible')
        })

        $(hiddenMethods).each(function(i, method) {
            $(method).slideDown();
        })

        $(endpoint).toggleClass('expanded', true);
        var $actived = $('.active');
        $actived.toggleClass('active');
        $(endpoint).toggleClass('active');

    });

    // Toggle headers section
    $('div.headers h4').click(function(event) {
        event.preventDefault();

        $(this.parentNode).toggleClass('expanded');

        $('div.fields', this.parentNode).slideToggle();
    });

    // Auth with OAuth
    $('#credentials').submit(function(event) {
        event.preventDefault();

        var params = $(this).serializeArray();

        $.post('/auth', params, function(result) {
            if (result.signin) {
                window.open(result.signin,"_blank","height=900,width=800,menubar=0,resizable=1,scrollbars=1,status=0,titlebar=0,toolbar=0");
            }
        })
    });

    /*
        Try it! button. Submits the method params, apikey and secret if any, and apiName
    */
    $('li.method form').submit(function(event) {
        var self = this;

        event.preventDefault();

        var params = $(this).serializeArray(),
            apiKey = { name: 'apiKey', value: $('input[name=key]').val() },
            apiSecret = { name: 'apiSecret', value: $('input[name=secret]').val() },
            apiName = { name: 'apiName', value: $('input[name=apiName]').val() };

        params.push(apiKey, apiSecret, apiName);

        // Setup results container
        var resultContainer = $('.result', self);
        if (resultContainer.length === 0) {
            resultContainer = $(document.createElement('div')).attr('class', 'result');
            $(self).append(resultContainer);
        }

        if ($('pre.response', resultContainer).length === 0) {

            // Clear results link
            var clearLink = $(document.createElement('a'))
                .text('Clear results')
                .addClass('clear-results')
                .attr('href', '#')
                .click(function(e) {
                    e.preventDefault();

                    var thislink = this;
                    $('.result', self)
                        .slideUp(function() {
                            $(this).remove();
                            $(thislink).remove();
                        });
                })
                .insertAfter($('input[type=submit]', self));

            // Call that was made, add pre elements
            resultContainer.append($(document.createElement('h4')).text('Call'));
            resultContainer.append($(document.createElement('pre')).addClass('call'));

            // Code
            resultContainer.append($(document.createElement('h4')).text('Response Code'));
            resultContainer.append($(document.createElement('pre')).addClass('code prettyprint'));

            // Header
            resultContainer.append($(document.createElement('h4')).text('Response Headers'));
            resultContainer.append($(document.createElement('pre')).addClass('headers prettyprint'));

            // Response
            resultContainer.append($(document.createElement('h4'))
                .text('Response Body')
                .append($(document.createElement('a'))
                    .text('Select body')
                    .addClass('select-all')
                    .attr('href', '#')
                    .click(function(e) {
                        e.preventDefault();
                        selectElementText($(this.parentNode).siblings('.response')[0]);
                    })
                )
            );

            resultContainer.append($(document.createElement('pre'))
                .addClass('response prettyprint'));
        }

        console.log(params);

        $.post('/processReq', params, function(result, text) {
            // If we get passed a signin property, open a window to allow the user to signin/link their account
            if (result.signin) {
                window.open(result.signin,"_blank","height=900,width=800,menubar=0,resizable=1,scrollbars=1,status=0,titlebar=0,toolbar=0");
            } else {
                var response,
                    responseContentType = result.headers['content-type'];
                // Format output according to content-type
                response = livedocs.formatData(result.response, result.headers['content-type'])

                $('pre.response', resultContainer)
                    .toggleClass('error', false)
                    .text(response);
            }

        })
        // Complete, runs on error and success
        .complete(function(result, text) {
            var response = JSON.parse(result.responseText);
            var method = "";

            params.forEach(function(param){
                if(param.name == "methodUri"){
                    method = param.value;
                }
            });

            if (response.call) {
                $('pre.call', resultContainer)
                    .text(response.call);
            }

            if (response.code) {
                $('pre.code', resultContainer)
                    .text(response.code);
            }

            if (response.headers) {
                $('pre.headers', resultContainer)
                    .text(formatJSON(response.headers));
            }

            if(method == "/Auth" || method == "/Auth/renew"){
                var result_response = JSON.parse(response.response)
                if(result_response.success == true){
                    var inputs, index;

                    inputs = document.getElementsByName('params[token]');
                    for (index = 0; index < inputs.length; ++index) {
                        inputs[index].value = result_response.response.token;
                    }
                }

            }

            // Syntax highlighting
            prettyPrint();
        })
        .error(function(err, text) {
            var response;

            if (err.responseText !== '') {
                var result = JSON.parse(err.responseText),
                    headers = formatJSON(result.headers);

                if (result.headers && result.headers['content-type']) {
                    // Format the result.response and assign it to response
                    response = livedocs.formatData(result.response, result.headers['content-type']);
                } else {
                    response = result.response;
                }

            } else {
                response = 'Error';
            }

            $('pre.response', resultContainer)
                .toggleClass('error', true)
                .text(response);
        })
    })

})();
