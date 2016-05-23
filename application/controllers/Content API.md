# Content API
## Retrieve Content
Retrieve content(s) by specified filter fields
#### HTTPMethod
GET
#### URI
/Content
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description | 
 | --- | --- | --- |--- |
| id | string | YES | Specific id of content | 
| title | string | YES | Specific title of content | 
| category | string | YES | Specific category name of content | 
| date_check | string | YES | Return content that available in this date range | 
| sort | string | YES | Specific field to sort ('title', 'date_start', 'date_end', 'date_added', 'date_modified', 'random') | 
| order | string | YES | Direction to order ('desc', 'asc'), but if sort is 'random' then this field will be seed number | 
| offset | integer | YES | number of records starting | 
| limit | integer | YES | number of results to return | 
| full_html | string | YES | true, will return full html | 
| pin | string | YES | Secret PIN given to content | 
| tags | string | YES | Specific tag(s) to find (e.g. foo,bar) | 
| player_id | string | YES | display content with given player id | 
| only_new_content | enumerated | YES | true = display new content with [player_id] | 
#### Response
| Name | Type | Nullable | Description | Format| 
| --- | --- | --- | --- | --- |
#### Response Example
```json 

 ```
#### Error Response
| Name | Error Code | Message | 
 | --- | --- | --- |
## Count Content
Count content(s) by specified filter fields
#### HTTPMethod
GET
#### URI
/Content/count
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description | 
 | --- | --- | --- |--- |
| category | string | YES | Specific category name of content | 
| player_exclude | string | YES | Find content which no activity with player | 
#### Response
| Name | Type | Nullable | Description | Format| 
| --- | --- | --- | --- | --- |
#### Response Example
```json 

 ```
#### Error Response
| Name | Error Code | Message | 
 | --- | --- | --- |
## Create content
Create content
#### HTTPMethod
POST
#### URI
/Content/addContent
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description | 
 | --- | --- | --- |--- |
| token | string | YES | access token returned from Auth | 
| title | string | YES | Specific title of content | 
| summary | string | YES | Specific summary of content | 
| detail | string | YES | Specific detail of content | 
| category | string | YES | Specific category name of content | 
| image | string | YES | url to the content profile image | 
| status | string | YES | content available status | 
| date_start | string | YES | date start in the format YYYY-MM-DD (ex.1982-09-29) | 
| date_end | string | YES | date end in the format YYYY-MM-DD (ex.1982-09-29) | 
| player_id | string | YES | player id who generate this content | 
| pin | string | YES | Secret PIN given to content | 
| tags | string | YES | Specific tag(s) to add (e.g. foo,bar) | 
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
## Update content
Update content by content ID
#### HTTPMethod
POST
#### URI
/Content/:content_id/update
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description | 
 | --- | --- | --- |--- |
| token | string | YES | access token returned from Auth | 
| content_id | string | YES | content id as used in client's content | 
| title | string | YES | Specific title of content | 
| summary | string | YES | Specific summary of content | 
| detail | string | YES | Specific detail of content | 
| category | string | YES | Specific category name of content | 
| image | string | YES | url to the content profile image | 
| status | string | YES | content available status | 
| date_start | string | YES | date start in the format YYYY-MM-DD (ex.1982-09-29) | 
| date_end | string | YES | date end in the format YYYY-MM-DD (ex.1982-09-29) | 
| pin | string | YES | Secret PIN given to content | 
| tags | string | YES | Specific tag(s) to update (e.g. foo,bar) | 
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
## Delete content
Delete existing content
#### HTTPMethod
POST
#### URI
/Content/:content_id/delete
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description | 
 | --- | --- | --- |--- |
| token | string | YES | access token returned from Auth | 
| content_id | string | YES | content id as used in client's content | 
#### Response
| Name | Type | Nullable | Description | Format| 
| --- | --- | --- | --- | --- |
#### Response Example
```json 

 ```
#### Error Response
| Name | Error Code | Message | 
 | --- | --- | --- |
## Retrieve Category
Retrieve category by specified filter fields
#### HTTPMethod
GET
#### URI
/Content/category
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description | 
 | --- | --- | --- |--- |
| id | string | YES | Specific id of category | 
| name | string | YES | Specific name of category | 
| sort | string | YES | Specific field to sort ('_id', 'name', 'date_added', 'date_modified') | 
| order | string | YES | Direction to order ('desc', 'asc') | 
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
## Create Category
Create content category
#### HTTPMethod
POST
#### URI
/Content/category/create
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description | 
 | --- | --- | --- |--- |
| token | string | YES | access token returned from Auth | 
| name | string | YES | Specific name of category | 
#### Response
| Name | Type | Nullable | Description | Format| 
| --- | --- | --- | --- | --- |
#### Response Example
```json 

 ```
#### Error Response
| Name | Error Code | Message | 
 | --- | --- | --- |
## Update Category
Update content category
#### HTTPMethod
POST
#### URI
/Content/category/update
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description | 
 | --- | --- | --- |--- |
| token | string | YES | access token returned from Auth | 
| id | string | YES | Specific id of category | 
| name | string | YES | Specific new name of category | 
#### Response
| Name | Type | Nullable | Description | Format| 
| --- | --- | --- | --- | --- |
#### Response Example
```json 

 ```
#### Error Response
| Name | Error Code | Message | 
 | --- | --- | --- |
## Delete Category
Delete content category
#### HTTPMethod
POST
#### URI
/Content/category/delete
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description | 
 | --- | --- | --- |--- |
| token | string | YES | access token returned from Auth | 
| id | string | YES | Specific id of category | 
#### Response
| Name | Type | Nullable | Description | Format| 
| --- | --- | --- | --- | --- |
#### Response Example
```json 

 ```
#### Error Response
| Name | Error Code | Message | 
 | --- | --- | --- |
## Action Like
Player send Like action to content
#### HTTPMethod
POST
#### URI
/Content/:content_id/player/:player_id/like
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description | 
 | --- | --- | --- |--- |
| token | string | YES | access token returned from Auth | 
| content_id | string | YES | content id as used in client's content | 
| player_id | string | YES | player id as used in client's website | 
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
## Action Dislike
Player send Dislike action to content
#### HTTPMethod
POST
#### URI
/Content/:content_id/player/:player_id/dislike
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description | 
 | --- | --- | --- |--- |
| token | string | YES | access token returned from Auth | 
| content_id | string | YES | content id as used in client's content | 
| player_id | string | YES | player id as used in client's website | 
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
## Give feedback
Player give any feedback(s) to content
#### HTTPMethod
POST
#### URI
/Content/:content_id/player/:player_id/feedback
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description | 
 | --- | --- | --- |--- |
| token | string | YES | access token returned from Auth | 
| content_id | string | YES | content id as used in client's content | 
| player_id | string | YES | player id as used in client's website | 
| feedback | string | YES | feedback for player given to content | 
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
