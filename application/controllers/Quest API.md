# Quest API
## Quest List Info
Returns information about all quest for the current site.
#### HTTPMethod
GET
#### URI
/Quest
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
| --- | --- | --- |--- |
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
## Quest Info
Returns information about the quest with the specified id.
#### HTTPMethod
GET
#### URI
/Quest/:id
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
| --- | --- | --- |--- |
| id | string | YES | quest id to query | 
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json 

 ```
#### Error Response
| Name | Error Code | Message |
| --- | --- | --- |
## Mission Info
Returns information about the mission with the specified id.
#### HTTPMethod
GET
#### URI
/Quest/:id/mission/:mission_id
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
| --- | --- | --- |--- |
| id | string | YES | quest id to query | 
| mission_id | string | YES | mission id to query | 
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json 

 ```
#### Error Response
| Name | Error Code | Message |
| --- | --- | --- |
## Quest List Available For Player
Returns information about list of quest is available for player.
#### HTTPMethod
GET
#### URI
/Quest/available
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
| --- | --- | --- |--- |
| player_id | string | YES | player id as used in client's website | 
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json 

 ```
#### Error Response
| Name | Error Code | Message |
| --- | --- | --- |
## Quest Available For Player
Returns information about the quest is available for player.
#### HTTPMethod
GET
#### URI
/Quest/:id/available
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
| --- | --- | --- |--- |
| id | string | YES | quest id to query | 
| player_id | string | YES | player id as used in client's website | 
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json 

 ```
#### Error Response
| Name | Error Code | Message |
| --- | --- | --- |
## Join Quest
Player join quest.
#### HTTPMethod
POST
#### URI
/Quest/:id/join
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
| --- | --- | --- |--- |
| token | string | YES | access token returned from Auth | 
| id | string | YES | quest id as player need to join | 
| player_id | string | YES | player id as used in client's website | 
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json 

 ```
#### Error Response
| Name | Error Code | Message |
| --- | --- | --- |
## Join All Quests
Player join all available quests.
#### HTTPMethod
POST
#### URI
/Quest/joinAll
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
| --- | --- | --- |--- |
| token | string | YES | access token returned from Auth | 
| player_id | string | YES | player id as used in client's website | 
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json 

 ```
#### Error Response
| Name | Error Code | Message |
| --- | --- | --- |
## Cancel Quest
Player cancel quest.
#### HTTPMethod
POST
#### URI
/Quest/:id/cancel
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
| --- | --- | --- |--- |
| token | string | YES | access token returned from Auth | 
| id | string | YES | quest id as player need to join | 
| player_id | string | YES | player id as used in client's website | 
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json 

 ```
#### Error Response
| Name | Error Code | Message |
| --- | --- | --- |
## Reset a Player's Quest
Reset a quest done by a player.
#### HTTPMethod
POST
#### URI
/Quest/reset
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
| --- | --- | --- |--- |
| token | string | YES | access token returned from Auth | 
| player_id | string | YES | player id as used in client's website | 
| quest_id | string | YES | quest id | 
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json 

 ```
#### Error Response
| Name | Error Code | Message |
| --- | --- | --- |
