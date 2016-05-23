# Service API
## Recent Point
Returns recent points
#### HTTPMethod
GET
#### URI
/Service/recent_point
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description | 
 | --- | --- | --- |--- |
| point_name | string | YES | name of the point-based reward to query | 
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
## Reset Point
Reset point of all players
#### HTTPMethod
POST
#### URI
/Service/reset_point
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description | 
 | --- | --- | --- |--- |
| token | string | YES | access token returned from Auth | 
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
