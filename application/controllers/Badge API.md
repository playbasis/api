# Badge API
## Badges Info
Returns information about all available badges for the current site.
#### HTTPMethod
GET
#### URI
/Badges
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
## Badge Info
Returns information about the badge with the specified id.
#### HTTPMethod
GET
#### URI
/Badge/:id
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
| --- | --- | --- |--- |
| id | integer | YES | badge id to query | 
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json 

 ```
#### Error Response
| Name | Error Code | Message |
| --- | --- | --- |
