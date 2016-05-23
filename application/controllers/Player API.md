# Player API
## Player Info (public data only)
Get public information about a player.
#### HTTPMethod
GET
#### URI
/Player/:id
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
| --- | --- | --- | --- |
| id | string | YES | player id as used in client's website |

#### Response
| Name | Type | Nullable | Description | Format |
| --- | --- | --- | --- |  --- |
| player | [player](#playerObject) | NO | JSON object of player |

### <a name="playerObject"></a>Response: player
| Name | Type | Nullable | Description | Format |
| --- | --- | --- | --- | --- |
| username | `string` | NO | player username as used in client's website |
| first_name | `string` | YES | player's first name |
| last_name | `string` | YES | player's last name |
| gender | `int` | NO | 0 = undefined, 1 = male, 2 = female |
| image | `url` | NO | player image, return as url |
| exp | `int` | NO | player experience point |
| level| `int` | NO | player id as used in client's website |
| date_added | `DateTime` | NO | Date that player was added | YYYY-MM-DDThh:mm:ssTZD |
| birth_date | `DateTime` | YES | player's birth date | YYYY-MM-DDThh:mm:ssTZD |
| last_login | `DateTime` | NO | Date that player last login | YYYY-MM-DDThh:mm:ssTZD |
| last_logout | `DateTime` | NO | Date that player last logout | YYYY-MM-DDThh:mm:ssTZD |
| cl_player_id | `string` | NO | player id as used in client's website |

#### Response Example
```json
"player": {
  "image": "http://elasticbeanstalk-ap-southeast-1-007834438823.s3.amazonaws.com/user_content/thumb/3f1bda9cc3707a9ae53b0a4157ea8681.png",
  "username": "1",
  "exp": 3865,
  "level": 12,
  "first_name": "1",
  "last_name": "Sven",
  "gender": 0,
  "birth_date": null,
  "registered": "2016-01-22T12:35:16+0700",
  "last_login": "2016-05-11T15:11:01+0700",
  "last_logout": "2016-05-16T10:57:20+0700",
  "cl_player_id": "1"
}
 ```
#### Error Response
| Name | Error Code | Message |
| --- | --- | --- |
| Invalid parameter |0903 | Invalid parameter id require , must not be blank or special |
| User doesn't exist |0200 | User doesn't exist |

## Player Info (include private data)
Get public and private information about a player.
#### HTTPMethod
POST
#### URI
/Player/:id
#### RequiresOAuth
NO
#### Parameters
Name | Type | Required | Description
| ---| --- | --- | --- |
| token | string | YES | access token returned from Auth |
| id | string | YES | player id as used in client's website |

#### Response
| Name | Type | Nullable | Description | Format |
| --- | --- | ---| --- | --- |
| player | [player](#playerObjectPrivate) | NO | JSON object of player |

### <a name="playerObjectPrivate"></a>Response: player
| Name | Type | Nullable | Description | Format |
| --- | --- | --- | --- | --- |
| username | `string` | NO | player username as used in client's website |
| first_name | `string` | YES | player's first name |
| last_name | `string` | YES | player's last name |
| gender | `int` | NO | 0 = undefined, 1 = male, 2 = female |
| image | `url` | NO | player image, return as url |
| email | `string` | NO | player's email address |
| phone_number | `string` | YES | player's phone number |
| exp | `int` | NO | player experience point |
| level| `int` | NO | player id as used in client's website |
| registered | `DateTime` | NO | Date that player was added | YYYY-MM-DDThh:mm:ssTZD |
| birth_date | `DateTime` | YES | player's birth date | YYYY-MM-DDThh:mm:ssTZD |
| last_login | `DateTime` | NO | Date that player last login | YYYY-MM-DDThh:mm:ssTZD |
| last_logout | `DateTime` | NO | Date that player last logout | YYYY-MM-DDThh:mm:ssTZD |
| cl_player_id | `string` | NO | player id as used in client's website |

#### Response Example
```json
"player": {
            "image": "http://elasticbeanstalk-ap-southeast-1-007834438823.s3.amazonaws.com/user_content/thumb/3f1bda9cc3707a9ae53b0a4157ea8681.png",
            "email": "pechpras@playbasis.com",
            "username": "1",
            "exp": 3865,
            "level": 12,
            "phone_number": null,
            "first_name": "1",
            "last_name": "Sven",
            "gender": 0,
            "birth_date": null,
            "registered": "2016-01-22T12:35:16+0700",
            "last_login": "2016-05-11T15:11:01+0700",
            "last_logout": "2016-05-16T10:57:20+0700",
            "cl_player_id": "1"
        }
 ```
#### Error Response
| Name | Error Code | Message |
| --- | --- | --- |
| Invalid parameter |0903 | Invalid parameter id require , must not be blank or special |
| User doesn't exist |0200 | User doesn't exist |

## List Player (Basic information)
Get basic information of players.
#### HTTPMethod
POST
#### URI
/Player/list
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
| --- | --- | --- | --- |
| token | string | YES | access token returned from Auth |
| list_player_id | string | YES | player id as used in client's website separate with ',' example '1,2,3' |
#### Response
| Name | Type | Nullable | Description | Format |
| --- | --- | --- | --- |  --- |
| player | [\[player\]](#playerArrayObjectPrivate) | NO | JSON array of player |

### <a name="playerObjectPrivate"></a>Response: player
| Name | Type | Nullable | Description | Format |
| --- | --- | --- | --- |  --- |
| username | `string` | NO | player username as used in client's website |
| first_name | `string` | YES | player's first name |
| last_name | `string` | YES | player's last name |
| gender | `int` | NO | 0 = undefined, 1 = male, 2 = female |
| image | `url` | NO | player image, return as url |
| email | `string` | NO | player's email address |
| phone_number | `string` | YES | player's phone number |
| exp | `int` | NO | player experience point |
| level| `int` | NO | player id as used in client's website |
| registered | `DateTime` | NO | Date that player was added | YYYY-MM-DDThh:mm:ssTZD |
| birth_date | `DateTime` | YES | player's birth date | YYYY-MM-DDThh:mm:ssTZD |
| last_login | `DateTime` | NO | Date that player last login | YYYY-MM-DDThh:mm:ssTZD |
| last_logout | `DateTime` | NO | Date that player last logout | YYYY-MM-DDThh:mm:ssTZD |
| cl_player_id | `string` | NO | player id as used in client's website |

#### Response Example
```json
"player": [
            {
                "cl_player_id": "1",
                "image": "http://elasticbeanstalk-ap-southeast-1-007834438823.s3.amazonaws.com/user_content/thumb/3f1bda9cc3707a9ae53b0a4157ea8681.png",
                "email": "pechpras@playbasis.com",
                "username": "1",
                "exp": 3865,
                "level": 12,
                "phone_number": null,
                "first_name": "1",
                "last_name": "Sven",
                "gender": 0,
                "birth_date": null,
                "registered": "2016-01-22T12:35:16+0700",
                "last_login": "2016-05-11T15:11:01+0700",
                "last_logout": "2016-05-16T10:57:20+0700"
            },
            {
                "cl_player_id": "2",
                "image": "https://randomuser.me/api/portraits/thumb/men/78.jpg",
                "email": "2@playbasis.com",
                "username": "2",
                "exp": 690,
                "level": 5,
                "phone_number": null,
                "first_name": "2",
                "last_name": "Sven",
                "gender": 0,
                "birth_date": null,
                "registered": "2016-01-22T12:36:01+0700",
                "last_login": "0000-00-00 00:00:00",
                "last_logout": "0000-00-00 00:00:00"
            }
        ]
 ```
#### Error Response
| Name | Type | Description | Format
| ---|:---:|--- :| ---
| empty | `empty array` | if something went wrong with an id, it won't return anything|

## Detailed Player Info (public data only)
Get detailed public information about a player, including points and badges.
#### HTTPMethod
GET
#### URI
/Player/:id/data/all
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
| --- | --- | --- | --- |
| id | string | YES | player id as used in client's website |

#### Response
| Name | Type | Nullable | Description | Format |
| --- | --- | --- | --- | --- |
| image | `url` | NO | player image, return as url |
| username | `string` | NO | player username as used in client's website |
| exp | `int` | NO | player experience point |
| level| `int` | NO | player id as used in client's website |
| first_name | `string` | YES | player's first name |
| last_name | `string` | YES | player's last name |
| gender | `int` | NO | 0 = undefined, 1 = male, 2 = female |
| birth_date | `DateTime` | YES | player's birth date | YYYY-MM-DDThh:mm:ssTZD |
| registered | `DateTime` | NO | Date that player was added | YYYY-MM-DDThh:mm:ssTZD |
| percent_of_level | `double` | NO | percentage of experience of current level |
| level_title  | `string` | NO | title of level |
| level_image | `url` | YES | image of current level title |
| last_login | `DateTime` | NO | Date that player last login | YYYY-MM-DDThh:mm:ssTZD |
| last_logout | `DateTime` | NO | Date that player last logout | YYYY-MM-DDThh:mm:ssTZD |
| badges | [\[badges\]](#badgeObject)| | NO | list of badge that player possess |
| goods | [\[goods\]](#goodsObject)| | NO | list of goods that player possess |
| points | [\[points\]](#pointObject)| | NO | list of points(point, gold, exp) that player possess |


### <a name="badgeObject"></a>Response: badge
| Name | Type | Nullable | Description | Format
| --- | --- | --- | --- | --- |
| badge_id | `string` | NO | Badge id |
| image | `url` | NO | Badge image, return as url |
| name | `string` | NO | Badge name |
| description | `html string` | NO | Badge description |
| amount | `int` | NO | Badge amount |
| hint | `string` | NO | Badge hint |
| tags | `string` | YES | Badge tags |

### <a name="goodsObject"></a>Response: goods
Name | Type | Nullable | Description | Format
| --- | --- | --- | --- | --- |
| goods_id | `string` | NO | Goods id |
| image | `url` | NO | Goods image, return as url |
| name | `string` | NO | Goods name |
| description | `html string` | NO | Goods description |
| code | `string` | NO | Goods redeem code |
| amount | `int` | NO | Goods amount |

### <a name="pointObject"></a>Response: point
| Name | Type | Nullable | Description | Format
| --- | --- | --- | --- | --- |
| reward_id | `string` | NO | Reward id |
| reward_name | `string` | NO | Reward name (gold, exp, point) |
| value | `int` | NO | Goods name |

#### Response Example
```json
"image": "http://elasticbeanstalk-ap-southeast-1-007834438823.s3.amazonaws.com/user_content/thumb/3f1bda9cc3707a9ae53b0a4157ea8681.png",
            "username": "1",
            "exp": 3865,
            "level": 12,
            "first_name": "1",
            "last_name": "Sven",
            "gender": 0,
            "birth_date": null,
            "registered": "2016-01-22T12:35:16+0700",
            "percent_of_level": 94.17,
            "level_title": "Experienced Novice",
            "level_image": "",
            "badges": [
                {
                    "badge_id": "5694c861472af27a048b5bfa",
                    "image": "http://images.pbapp.net/data/dcad0d98ffd4b1c7d8cc30ef23b6b3ae.png",
                    "name": "Quiz Beginner",
                    "description": "<p>Great! You have completed 3 quizzes.</p>\n",
                    "amount": 1,
                    "hint": "",
                    "tags": null
                },
                {
                    "badge_id": "5694dbdb472af27c048b5c64",
                    "image": "http://images.pbapp.net/data/46ab23a9cd3d1ca137967bcd4edddf46.png",
                    "name": "View Content Beginner",
                    "description": "<p>Great! You have completed 3 contents.</p>\n",
                    "amount": 1,
                    "hint": "",
                    "tags": null
                },
                {
                    "badge_id": "5694dc15472af2eb068b5c74",
                    "image": "http://images.pbapp.net/data/dcce984bf18868f4c2535030f4451164.png",
                    "name": "View Content Contributor",
                    "description": "<p>Amazing! You have completed 10 contents.</p>\n",
                    "amount": 1,
                    "hint": "",
                    "tags": null
                },
                {
                    "badge_id": "5694dc3e472af2eb068b5c76",
                    "image": "http://images.pbapp.net/data/3aa0be610892576960f2818df4bed358.png",
                    "name": "View Content Aficionado",
                    "description": "<p>Awesome! You have completed 20 contents.</p>\n",
                    "amount": 1,
                    "hint": "",
                    "tags": null
                },
                {
                    "badge_id": "5694de28472af26e2e8b5c0b",
                    "image": "http://images.pbapp.net/data/99c119c73abc764ec4272ada82ca6335.png",
                    "name": "Sales Beginner",
                    "description": "<p>Great! You have done 3 selling.</p>\n",
                    "amount": 1,
                    "hint": "",
                    "tags": null
                },
                {
                    "badge_id": "5694de68472af2eb068b5c7a",
                    "image": "http://images.pbapp.net/data/bdcb5fc245fa46cc43c1060a366bd50e.png",
                    "name": "Sales Contributor",
                    "description": "<p>Amazing! You have done 10 selling.</p>\n",
                    "amount": 1,
                    "hint": "",
                    "tags": null
                }
            ],
            "goods": [
                {
                    "goods_id": "56979fc4472af279048b5ca4",
                    "image": "http://images.pbapp.net/data/e6c474f31d6bcc0556260bcf6368c12c.jpg",
                    "name": "Iphone 6S 16GB",
                    "description": "<p>Iphone 6S 16GB</p>\n",
                    "code": "DJNR9324E",
                    "amount": 2
                },
                {
                    "goods_id": "5697a1a2472af270068b5c6c",
                    "image": "http://images.pbapp.net/data/a7d76cb4ea3759520d751a032e984a6f.jpg",
                    "name": "Starbuck Coffee Gift Card $10",
                    "description": "<p>Starbuck Coffee Gift Card $10</p>\n",
                    "code": "SARBBL68768",
                    "amount": 1
                },
                {
                    "goods_id": "5697a2db472af2dc0a8b5c10",
                    "image": "http://images.pbapp.net/data/e221cd768997b0cd9c5f13e7ed3a29ca.jpg",
                    "name": "Pizza",
                    "description": "<p>Pizza from Pizza Hut</p>\n",
                    "code": "DSREB98231",
                    "amount": 1
                }
            ],
            "points": [
                {
                    "reward_id": "569368ad472af2eb068b5bed",
                    "reward_name": "gold",
                    "value": 17792
                },
                {
                    "reward_id": "52ea1ea78d8c89401c0000b5",
                    "reward_name": "exp",
                    "value": 3865
                },
                {
                    "reward_id": "52ea1ea78d8c89401c0000b4",
                    "reward_name": "point",
                    "value": 4410
                }
            ],
            "last_login": "2016-05-11T15:11:01+0700",
            "last_logout": "2016-05-16T10:57:20+0700"
        }
    },
 ```
#### Error Response
Name | Error Code | Message
---|:---: |:---
## Detailed Player Info (include private data)
Get detailed public and private information about a player, including points and badges
#### HTTPMethod
POST
#### URI
/Player/:id/data/all
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
 | --- | --- | --- |--- |
| token | string | YES | access token returned from Auth |
| id | string | YES | player id as used in client's website |
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json

 ```
#### Error Response
| Name | Error Code | Message |
 | --- | --- | --- |
## List Custom Fields of Player
Get custom fields information about a player
#### HTTPMethod
GET
#### URI
/Player/:id/custom
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
 | --- | --- | --- |--- |
| id | string | YES | player id as used in client's website |
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json

 ```
#### Error Response
| Name | Error Code | Message |
 | --- | --- | --- |
## Set Custom Field of Player
Set custom field of a player
#### HTTPMethod
POST
#### URI
/Player/:id/custom
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
 | --- | --- | --- |--- |
| token | string | YES | access token returned from Auth |
| id | string | YES | player id as used in client's website |
| key | string | YES | custom field keys separated by comma |
| value | string | YES | custom field values separated by comma |
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json

 ```
#### Error Response
| Name | Error Code | Message |
 | --- | --- | --- |
## Register
Register a user from client's website as a Playbasis player.
#### HTTPMethod
POST
#### URI
/Player/:id/register
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
 | --- | --- | --- |--- |
| token | string | YES | access token returned from Auth |
| id | string | YES | player id as used in client's website *** It's available only [A-Za-z0-9_-] |
| username | string | YES | username of the player |
| email | string | YES | email address of the player |
| image | string | YES | url to the player profile image |
| phone_number | string | YES | +66xxyyyzzzz |
| facebook_id | string | YES | facebook id of the player |
| twitter_id | string | YES | twitter id of the player |
| password | string | YES | password of the player |
| first_name | string | YES | first name of the player |
| last_name | string | YES | last name of the player |
| gender | enumerated | YES | 1=Male, 2=Female |
| birth_date | string | YES | date of birth in the format YYYY-MM-DD (ex.1982-09-29) |
| code | string | YES | referral code of another player for invitation system |
| anonymous | enumerated | YES | anonymous flag |
| device_id | string | YES | device id to verify with SMS verification process |
| approve_status | enumerated | YES | approval status |
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json

 ```
#### Error Response
| Name | Error Code | Message |
 | --- | --- | --- |
## Update
Update player information.
#### HTTPMethod
POST
#### URI
/Player/:id/update
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
 | --- | --- | --- |--- |
| token | string | YES | access token returned from Auth |
| id | string | YES | player id as used in client's website |
| username | string | YES | username of the player |
| email | string | YES | email address of the player |
| image | string | YES | url to the player profile image |
| phone_number | string | YES | +66xxyyyzzzz |
| exp | number | YES | player's experience points |
| level | number | YES | player's level |
| facebook_id | string | YES | facebook id of the player |
| twitter_id | string | YES | twitter id of the player |
| password | string | YES | password of the player |
| first_name | string | YES | first name of the player |
| last_name | string | YES | last name of the player |
| gender | enumerated | YES | 1=Male, 2=Female |
| birth_date | string | YES | date of birth in the format YYYY-MM-DD (ex.1982-09-29) |
| device_id | string | YES | device id to verify with SMS verification process |
| approve_status | enumerated | YES | approval status |
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json

 ```
#### Error Response
| Name | Error Code | Message |
 | --- | --- | --- |
## Reset player password
Reset player password that store in Playbasis system.
#### HTTPMethod
POST
#### URI
/Player/password/email
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
 | --- | --- | --- |--- |
| token | string | YES | access token returned from Auth |
| email | string | YES | email as used in client's website |
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json

 ```
#### Error Response
| Name | Error Code | Message |
 | --- | --- | --- |
## Verify player email
Verify player email that store in Playbasis system.
#### HTTPMethod
POST
#### URI
/Player/:id/email/verify
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
 | --- | --- | --- |--- |
| token | string | YES | access token returned from Auth |
| id | string | YES | player id as used in client's website |
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json

 ```
#### Error Response
| Name | Error Code | Message |
 | --- | --- | --- |
## Delete
Permenently delete a player from Playbasis database.
#### HTTPMethod
POST
#### URI
/Player/:id/delete
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
 | --- | --- | --- |--- |
| token | string | YES | access token returned from Auth |
| id | string | YES | player id as used in client's website |
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json

 ```
#### Error Response
| Name | Error Code | Message |
 | --- | --- | --- |
## Login
Tell Playbasis system that a player has logged in.
#### HTTPMethod
POST
#### URI
/Player/:id/login
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
 | --- | --- | --- |--- |
| token | string | YES | access token returned from Auth |
| id | string | YES | player id as used in client's website |
| session_id | string | YES | session id of the player |
| session_expires_in | string | YES | session expires in seconds |
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json

 ```
#### Error Response
| Name | Error Code | Message |
 | --- | --- | --- |
## Player Auth
Authenticate player with data in Playbasis system then login and also create session.
#### HTTPMethod
POST
#### URI
/Player/auth
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
 | --- | --- | --- |--- |
| token | string | YES | access token returned from Auth |
| email | string | YES | email as used in client's website (either email or username is required) |
| username | string | YES | username as used in client's website (either email or username is required) |
| password | string | YES | password of the player |
| device_token | string | YES | device token to verify with SMS verification process |
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json

 ```
#### Error Response
| Name | Error Code | Message |
 | --- | --- | --- |
## Request OTP
Request One time password.
#### HTTPMethod
POST
#### URI
/Player/auth/:id/requestOTPCode
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
 | --- | --- | --- |--- |
| token | string | YES | access token returned from Auth |
| id | string | YES | player id as used in client's website |
| device_token | string | YES | access token returned from Device |
| device_description | string | YES | Device model description |
| device_name | string | YES | Device model name |
| os_type | enumerated | YES | Choose os type |
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json

 ```
#### Error Response
| Name | Error Code | Message |
 | --- | --- | --- |
## Request OTP for setup phone
Request One time password for setup phone.
#### HTTPMethod
POST
#### URI
/Player/auth/:id/setupPhone
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
 | --- | --- | --- |--- |
| token | string | YES | access token returned from Auth |
| id | string | YES | player id as used in client's website |
| phone_number | string | YES | +66xxyyyzzzz |
| device_token | string | YES | access token returned from Device |
| device_description | string | YES | Device model description |
| device_name | string | YES | Device model name |
| os_type | enumerated | YES | Choose os type |
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json

 ```
#### Error Response
| Name | Error Code | Message |
 | --- | --- | --- |
## Perform OTP verification
Perform OTP verification from code that has sent to player SMS.
#### HTTPMethod
POST
#### URI
/Player/auth/:id/verifyOTPCode
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
 | --- | --- | --- |--- |
| token | string | YES | access token returned from Auth |
| id | string | YES | player id as use in client's site |
| code | string | YES | OTP code as sent to player |
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json

 ```
#### Error Response
| Name | Error Code | Message |
 | --- | --- | --- |
## Logout
Tell Playbasis system that a player has logged out.
#### HTTPMethod
POST
#### URI
/Player/:id/logout
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
 | --- | --- | --- |--- |
| token | string | YES | access token returned from Auth |
| id | string | YES | player id as used in client's website |
| session_id | string | YES | session id of the player |
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json

 ```
#### Error Response
| Name | Error Code | Message |
 | --- | --- | --- |
## List Active Player Sessions
List active sessions of a player in Playbasis system.
#### HTTPMethod
GET
#### URI
/Player/:id/sessions
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
 | --- | --- | --- |--- |
| id | string | YES | player id as used in client's website |
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json

 ```
#### Error Response
| Name | Error Code | Message |
 | --- | --- | --- |
## Find a Player by Session
Find a player given session ID.
#### HTTPMethod
GET
#### URI
/Player/session/:session_id
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
 | --- | --- | --- |--- |
| session_id | string | YES | session id |
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json

 ```
#### Error Response
| Name | Error Code | Message |
 | --- | --- | --- |
## Points
Returns information about all point-based rewards that a player currently have.
#### HTTPMethod
GET
#### URI
/Player/:id/points
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
 | --- | --- | --- |--- |
| id | string | YES | player id as used in client's website |
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json

 ```
#### Error Response
| Name | Error Code | Message |
 | --- | --- | --- |
## Point
Returns how much of specified the point-based reward a player currently have.
#### HTTPMethod
GET
#### URI
/Player/:id/point/:point_name
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
 | --- | --- | --- |--- |
| id | string | YES | player id as used in client's website |
| point_name | string | YES | name of the point-based reward to query |
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json

 ```
#### Error Response
| Name | Error Code | Message |
 | --- | --- | --- |
## Point History
Returns history points of player
#### HTTPMethod
GET
#### URI
/Player/:id/point_history
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
 | --- | --- | --- |--- |
| id | string | YES | player id as used in client's website |
| point_name | string | YES | name of the point-based reward to query |
| offset | integer | YES | number of records starting |
| limit | integer | YES | number of results to return |
| order | string | YES | Specify sorted direction [desc, *asc*] |
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json

 ```
#### Error Response
| Name | Error Code | Message |
 | --- | --- | --- |
## Action Time
Returns the last time that player performed the specified action.
#### HTTPMethod
GET
#### URI
/Player/:id/action/:action_name/time
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
 | --- | --- | --- |--- |
| id | string | YES | player id as used in client's website |
| action_name | string | YES | name of the action to query |
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json

 ```
#### Error Response
| Name | Error Code | Message |
 | --- | --- | --- |
## Last Action
Returns the time and action that a player last performed.
#### HTTPMethod
GET
#### URI
/Player/:id/action/time
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
 | --- | --- | --- |--- |
| id | string | YES | player id as used in client's website |
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json

 ```
#### Error Response
| Name | Error Code | Message |
 | --- | --- | --- |
## Action Count
Returns the number of times that a player has performed the specified action.
#### HTTPMethod
GET
#### URI
/Player/:id/action/:action_name/count
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
 | --- | --- | --- |--- |
| id | string | YES | player id as used in client's website |
| action_name | string | YES | name of the action to query |
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json

 ```
#### Error Response
| Name | Error Code | Message |
 | --- | --- | --- |
## Level
Returns detail of level.
#### HTTPMethod
GET
#### URI
/Player/level/:level
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
 | --- | --- | --- |--- |
| level | integer | YES | number of  level |
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json

 ```
#### Error Response
| Name | Error Code | Message |
 | --- | --- | --- |
## Levels
Returns all detail of level.
#### HTTPMethod
GET
#### URI
/Player/levels
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
 | --- | --- | --- |--- |
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json

 ```
#### Error Response
| Name | Error Code | Message |
 | --- | --- | --- |
## Badge
Returns information about all the badges that a player has earned.
#### HTTPMethod
GET
#### URI
/Player/:id/badge
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
 | --- | --- | --- |--- |
| id | string | YES | player id as used in client's website |
| tags | string | YES | Specific tag(s) to find (e.g. foo,bar) |
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json

 ```
#### Error Response
| Name | Error Code | Message |
 | --- | --- | --- |
## All Badges
Returns information about all the badges of the client as well as the amount that a player may earn.
#### HTTPMethod
GET
#### URI
/Player/:id/badgeAll
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
 | --- | --- | --- |--- |
| id | string | YES | player id as used in client's website |
| tags | string | YES | Specific tag(s) to find (e.g. foo,bar) |
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json

 ```
#### Error Response
| Name | Error Code | Message |
 | --- | --- | --- |
## Rank
Returns list of players sorted by the specified point type.
#### HTTPMethod
GET
#### URI
/Player/rank/:rank_by/:limit
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
 | --- | --- | --- |--- |
| rank_by | string | YES | name of point-based reward to rank players by |
| limit | integer | YES | number of results to return |
| mode | string | YES | weekly, monthly (default is all-time) |
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json

 ```
#### Error Response
| Name | Error Code | Message |
 | --- | --- | --- |
## DEPRECATED - Level
Returns detail of level.
#### HTTPMethod
POST
#### URI
/Player/level/:level
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
 | --- | --- | --- |--- |
| token | string | YES | access token returned from Auth |
| level | integer | YES | number of level |
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json

 ```
#### Error Response
| Name | Error Code | Message |
 | --- | --- | --- |
## DEPRECATED - Levels
Returns all detail of level.
#### HTTPMethod
POST
#### URI
/Player/levels
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
 | --- | --- | --- |--- |
| token | string | YES | access token returned from Auth |
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json

 ```
#### Error Response
| Name | Error Code | Message |
 | --- | --- | --- |
## Ranks
Returns list of players sorted by each point type.
#### HTTPMethod
GET
#### URI
/Player/ranks/:limit
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
 | --- | --- | --- |--- |
| limit | integer | YES | number of results to return for each point type |
| mode | string | YES | weekly, monthly (default is all-time) |
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json

 ```
#### Error Response
| Name | Error Code | Message |
 | --- | --- | --- |
## DEPRECATED - Ranks
Returns list of players sorted by each point type.
#### HTTPMethod
POST
#### URI
/Player/ranks/:limit
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
 | --- | --- | --- |--- |
| token | string | YES | access token returned from Auth |
| limit | integer | YES | number of results to return for each point type |
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json

 ```
#### Error Response
| Name | Error Code | Message |
 | --- | --- | --- |
## Goods
Returns information about all the goods list that a player has redeem.
#### HTTPMethod
GET
#### URI
/Player/:id/goods
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
 | --- | --- | --- |--- |
| id | string | YES | player id as used in client's website |
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json

 ```
#### Error Response
| Name | Error Code | Message |
 | --- | --- | --- |
## Quest of Player
Quest that the player has joined.
#### HTTPMethod
GET
#### URI
/Player/quest/:id
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
 | --- | --- | --- |--- |
| id | string | YES | quest id as player need to join |
| player_id | string | YES | player id as used in client's website |
| filter | string | YES | fields to be included (comma ',' as delimiter) |
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json

 ```
#### Error Response
| Name | Error Code | Message |
 | --- | --- | --- |
## Quest List of Player
List of quests that the player has joined.
#### HTTPMethod
GET
#### URI
/Player/quest
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
 | --- | --- | --- |--- |
| player_id | string | YES | player id as used in client's website |
| tags | string | YES | Specific tag(s) to find (e.g. foo,bar) |
| filter | string | YES | fields to be included (comma ',' as delimiter) |
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json

 ```
#### Error Response
| Name | Error Code | Message |
 | --- | --- | --- |
## All Quests of Player
List of all available quests of the client as well as the status of the player if joined.
#### HTTPMethod
GET
#### URI
/Player/questAll/:id
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
 | --- | --- | --- |--- |
| id | string | YES | player id as used in client's website |
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json

 ```
#### Error Response
| Name | Error Code | Message |
 | --- | --- | --- |
## Quest Reward History
Returns quest reward history of player
#### HTTPMethod
GET
#### URI
/Player/:id/quest_reward_history
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
 | --- | --- | --- |--- |
| id | string | YES | player id as used in client's website |
| offset | integer | YES | number of records starting |
| limit | integer | YES | number of results to return |
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json

 ```
#### Error Response
| Name | Error Code | Message |
 | --- | --- | --- |
## Deduct Reward
Deduct a reward from a given player.
#### HTTPMethod
POST
#### URI
/Player/:id/deduct
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
 | --- | --- | --- |--- |
| token | string | YES | access token returned from Auth |
| id | string | YES | player id as used in client's website |
| reward | string | YES | the name of the reward |
| amount | number | YES | amount |
| force | number | YES | 0 = not force if player has not enough reward to deduct, 1 = force to do the deduct (and player's reward becomes zero) |
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json

 ```
#### Error Response
| Name | Error Code | Message |
 | --- | --- | --- |
## Player Referral Code
Returns generated referral code of player
#### HTTPMethod
GET
#### URI
/Player/:id/code
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
 | --- | --- | --- |--- |
| id | string | YES | player id as used in client's website |
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json

 ```
#### Error Response
| Name | Error Code | Message |
 | --- | --- | --- |
## Rank by Custom parameter
Returns list of players sorted by rising custom parameter
#### HTTPMethod
GET
#### URI
/Player/rankParam/:action/:parameter
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
 | --- | --- | --- |--- |
| action | string | YES | action of parameter to rank players by |
| parameter | string | YES | name of parameter to rank players by |
| limit | integer | YES | number of results to return |
| month | string | YES | month to rank players by (01, 02, 03,..., 12) |
| year | string | YES | year to rank players by (2015, 2016 , ...) |
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json

 ```
#### Error Response
| Name | Error Code | Message |
 | --- | --- | --- |
## Get Associated Node
Get associated node of player
#### HTTPMethod
GET
#### URI
/Player/:id/getAssociatedNode
#### RequiresOAuth
YES
#### Parameters
| Name | Type | Required | Description |
 | --- | --- | --- |--- |
| id | string | YES | player id as used in client's website |
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json

 ```
#### Error Response
| Name | Error Code | Message |
 | --- | --- | --- |
## Get Role
Get role of player in specific node
#### HTTPMethod
GET
#### URI
/Player/:id/getRole/:node_id
#### RequiresOAuth
YES
#### Parameters
| Name | Type | Required | Description |
 | --- | --- | --- |--- |
| id | string | YES | player id as used in client's website |
| node_id | string | YES | Node Id to add player |
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json

 ```
#### Error Response
| Name | Error Code | Message |
 | --- | --- | --- |
## Sale report
Sale report of any node that associated with the player
#### HTTPMethod
GET
#### URI
/Player/:id/saleReport
#### RequiresOAuth
YES
#### Parameters
| Name | Type | Required | Description |
 | --- | --- | --- |--- |
| id | string | YES | player id as used in client's website |
| month | string | YES | Select month to get sale report(ex. 1,2,..,12)[default = current month] |
| year | string | YES | Select year to get sale report(ex. 2015)[default = current year] |
| action | string | YES | Select action name to query from action log [default = "sell"] |
| parameter | string | YES | Select parameter to report from action log [default = "amount"] |
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json

 ```
#### Error Response
| Name | Error Code | Message |
 | --- | --- | --- |
## Unlock
Unlock player for authentication
#### HTTPMethod
POST
#### URI
/Player/:id/unlock
#### RequiresOAuth
YES
#### Parameters
| Name | Type | Required | Description |
 | --- | --- | --- |--- |
| token | string | YES | access token returned from Auth |
| id | string | YES | player id as used in client's website |
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json

 ```
#### Error Response
| Name | Error Code | Message |
 | --- | --- | --- |
