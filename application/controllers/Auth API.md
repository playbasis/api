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
Name | Type | Required | Description 
 ---|:---:|--- :|---
api_key | string | YES | api key issued by Playbasis
api_secret | string | YES | api secret issued by Playbasis
#### Response
Name | Type | Nullable | Description | Format
---|:---:|--- :| ---
#### Response Example
```json 

 ```
#### Error Response
Name | Error Code | Message
---|:---: |:---
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
Name | Type | Nullable | Description | Format
---|:---:|--- :| ---
#### Response Example
```json 

 ```
#### Error Response
Name | Error Code | Message
---|:---: |:---
