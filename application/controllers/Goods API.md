# Goods API
## Goods List Info
Returns information about all available goods for the current site.
#### HTTPMethod
GET
#### URI
/Goods
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description | 
 | --- | --- | --- |--- |
| player_id | string | YES | player id as used in client's website | 
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
## Goods Info
Returns information about the goods with the specified id.
#### HTTPMethod
GET
#### URI
/Goods/:id
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description | 
 | --- | --- | --- |--- |
| id | string | YES | goods id to query | 
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
## Goods Group Available
Find number of available Goods given group.
#### HTTPMethod
GET
#### URI
/Redeem/goodsGroup
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description | 
 | --- | --- | --- |--- |
| player_id | string | YES | player id as used in client's website | 
| group | string | YES | goods group | 
| amount | integer | YES | amount of the goods to redeem | 
#### Response
| Name | Type | Nullable | Description | Format| 
| --- | --- | --- | --- | --- |
#### Response Example
```json 

 ```
#### Error Response
| Name | Error Code | Message | 
 | --- | --- | --- |
## Sponsored Goods List Info
Returns information about all available sponsored goods.
#### HTTPMethod
GET
#### URI
/Goods/sponsor
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
## Sponsored Goods Info
Returns information about the sponsored goods with the specified id.
#### HTTPMethod
GET
#### URI
/Goods/sponsor/:id
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description | 
 | --- | --- | --- |--- |
| id | string | YES | goods id to query | 
#### Response
| Name | Type | Nullable | Description | Format| 
| --- | --- | --- | --- | --- |
#### Response Example
```json 

 ```
#### Error Response
| Name | Error Code | Message | 
 | --- | --- | --- |
## Sponsored Goods Group Available
Find number of available sponsored Goods given group.
#### HTTPMethod
GET
#### URI
/Redeem/sponsorGroup
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description | 
 | --- | --- | --- |--- |
| player_id | string | YES | player id as used in client's website | 
| group | string | YES | goods group | 
| amount | integer | YES | amount of the goods to redeem | 
#### Response
| Name | Type | Nullable | Description | Format| 
| --- | --- | --- | --- | --- |
#### Response Example
```json 

 ```
#### Error Response
| Name | Error Code | Message | 
 | --- | --- | --- |
