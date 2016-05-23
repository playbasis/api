# Engine API
## List Rules
Returns list of active game rules defined for a client’s website.
#### HTTPMethod
GET
#### URI
/Engine/rules
#### RequiresOAuth
NO
#### Parameters
Name | Type | Required | Description 
 ---|:---:|--- :|---
action | string | YES | name of action performed
player_id | string | YES | player id as used in client's website
#### Response
Name | Type | Nullable | Description | Format
---|:---:|--- :| ---
#### Response Example
```json 

 ```
#### Error Response
Name | Error Code | Message
---|:---: |:---
## Rule Detail
Get the detail of the rule.
#### HTTPMethod
GET
#### URI
/Engine/rule/:id
#### RequiresOAuth
NO
#### Parameters
Name | Type | Required | Description 
 ---|:---:|--- :|---
id | string | YES | rule id
player_id | string | YES | player id as used in client's website
#### Response
Name | Type | Nullable | Description | Format
---|:---:|--- :| ---
#### Response Example
```json 

 ```
#### Error Response
Name | Error Code | Message
---|:---: |:---
## Rule
Process an action through all the game rules defined for a client’s website.
#### HTTPMethod
POST
#### URI
/Engine/rule
#### RequiresOAuth
NO
#### Parameters
Name | Type | Required | Description 
 ---|:---:|--- :|---
token | string | YES | access token returned from Auth
action | string | YES | name of action performed
player_id | string | YES | player id as used in client's website
url | string | YES | URL of the page that trigger the action or any identifier string - Used for logging, URL specific rules, and rules that trigger only when a specific identifier string is supplied
reward | string | YES | name of the point-based reward to give to player, if the action trigger custom-point reward that doesn't specify reward name
quantity | string | YES | amount of the point-based reward to give to player, if the action trigger custom-point reward that doesn't specify reward quantity
rule_id | string | YES | if needed, you can also specify a rule id so that rule engine will only process against that rule
node_id | string | YES | if needed, you can also specify a node id so that rule engine will process with that rule
session_id | string | YES | you can specify a session id to extend expire session time for that player
#### Response
Name | Type | Nullable | Description | Format
---|:---:|--- :| ---
#### Response Example
```json 

 ```
#### Error Response
Name | Error Code | Message
---|:---: |:---
