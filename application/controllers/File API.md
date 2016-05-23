# File API
## Retrieve file data
Retrieve image(s) content by specified filter fields
#### HTTPMethod
GET
#### URI
/File/list
#### RequiresOAuth
NO
#### Parameters
Name | Type | Required | Description 
 ---|:---:|--- :|---
id | string | YES | Specific id of file data
player_id | string | YES | Specific player_id of file data
sort | string | YES | Specific field to sort ('date_added', 'date_modified', 'type', 'file_size')
order | string | YES | Direction to order ('desc', 'asc')
#### Response
Name | Type | Nullable | Description | Format
---|:---:|--- :| ---
#### Response Example
```json 

 ```
#### Error Response
Name | Error Code | Message
---|:---: |:---
## Delete
Permenently delete a Image File from Playbasis database.
#### HTTPMethod
POST
#### URI
/File/delete
#### RequiresOAuth
NO
#### Parameters
Name | Type | Required | Description 
 ---|:---:|--- :|---
token | string | YES | access token returned from Auth
file_name | string | YES | image file name
#### Response
Name | Type | Nullable | Description | Format
---|:---:|--- :| ---
#### Response Example
```json 

 ```
#### Error Response
Name | Error Code | Message
---|:---: |:---
