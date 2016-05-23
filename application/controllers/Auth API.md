# Auth API
## Auth
Request access token from playbasis server.
#### HTTPMethod
POST
#### URI
/Auth
#### RequiresOAuth
NO
#### Parameters
Name | Type | Required | Description |
 ---|:---:|--- :|--- :|
api_key | string | YES | api key issued by Playbasis |
api_secret | string | YES | api secret issued by Playbasis |

#### Response
Name | Type | Nullable | Description | Format |
---|:---:|--- :| --- :|
token | `String` | NO | access token | |
date_expire | `DateTime` | NO | access token | YYYY-MM-DDThh:mm:ssTZD |

#### Response Example
```json
{    
    "token": "068af2b100a49e6824b8857bdc37b1a59126d964",
    "date_expire": "2016-05-26T10:38:21+0700"
}
 ```
#### Error Response
Name | Error Code | Message |
---|:---: |:--- |
Invalid API-KEY OR API-SECRET | 0001 | Invalid API-KEY OR API-SECRET

## Renew
Create a new token and discard the current token
#### HTTPMethod
POST
#### URI
/Auth/renew
#### RequiresOAuth
NO
#### Parameters
Name | Type | Required | Description
 ---|:---:|--- :|---
api_key | string | YES | api key issued by Playbasis
api_secret | string | YES | api secret issued by Playbasis

#### Response
Name | Type | Nullable | Description | Format |
---|:---:|--- :| ---:|
token | `String` | NO | access token | |
date_expire | `DateTime` | NO | access token | YYYY-MM-DDThh:mm:ssTZD |


#### Response Example
```json
{
  "token": "baaa6b86f119080fd8da5a0e6229497c02785bf0",
  "date_expire": "2016-05-26T19:22:29+0700"
}
 ```
#### Error Response
Name | Error Code | Message |
---|:---: |:--- |
Invalid API-KEY OR API-SECRET | 0001 | Invalid API-KEY OR API-SECRET
