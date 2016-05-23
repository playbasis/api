# Live Feed API
## Recent Activities
Returns recent activities
#### HTTPMethod
GET
#### URI
/Service/recentActivities
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description | 
 | --- | --- | --- |--- |
| player_id | string | YES | player id as used in client's website (required when mode='player') | 
| offset | integer | YES | number of records starting | 
| limit | integer | YES | number of results to return | 
| last_read_activity_id | string | YES | last activity id that you have read | 
| mode | string | YES | specify 'all' or 'player' (default is 'all') | 
#### Response
| Name | Type | Nullable | Description | Format| 
| --- | --- | --- | --- | --- |
#### Response Example
```json 

 ```
#### Error Response
| Name | Error Code | Message | 
 | --- | --- | --- |
## Detail Activity
Get detail activity
#### HTTPMethod
GET
#### URI
/Service/detailActivityFeed/:activity_id
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description | 
 | --- | --- | --- |--- |
| activity_id | string | YES | id of the activity | 
#### Response
| Name | Type | Nullable | Description | Format| 
| --- | --- | --- | --- | --- |
#### Response Example
```json 

 ```
#### Error Response
| Name | Error Code | Message | 
 | --- | --- | --- |
## Like Activity
Like activity
#### HTTPMethod
POST
#### URI
/Service/likeActivityFeed/:activity_id
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description | 
 | --- | --- | --- |--- |
| token | string | YES | access token returned from Auth | 
| activity_id | string | YES | id of the activity | 
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
## Comment Activity
Comment activity
#### HTTPMethod
POST
#### URI
/Service/commentActivityFeed/:activity_id
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description | 
 | --- | --- | --- |--- |
| token | string | YES | access token returned from Auth | 
| activity_id | string | YES | id of the activity | 
| player_id | string | YES | player id as used in client's website | 
| message | string | YES | comment message | 
#### Response
| Name | Type | Nullable | Description | Format| 
| --- | --- | --- | --- | --- |
#### Response Example
```json 

 ```
#### Error Response
| Name | Error Code | Message | 
 | --- | --- | --- |
