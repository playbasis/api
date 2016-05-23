# Redeem API
## Redeem
Redeem Goods for a client’s website.
#### HTTPMethod
POST
#### URI
/Redeem/goods
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description | 
 | --- | --- | --- |--- |
| token | string | YES | access token returned from Auth | 
| goods_id | string | YES | goods id of goods store | 
| player_id | string | YES | player id as used in client's website | 
| amount | integer | YES | amount of the goods to give to player | 
#### Response
| Name | Type | Nullable | Description | Format| 
| --- | --- | --- | --- | --- |
#### Response Example
```json 

 ```
#### Error Response
| Name | Error Code | Message | 
 | --- | --- | --- |
## Redeem Goods Group
Redeem Goods given group.
#### HTTPMethod
POST
#### URI
/Redeem/goodsGroup
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description | 
 | --- | --- | --- |--- |
| token | string | YES | access token returned from Auth | 
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
## Redeem Verification
Redeem verification for merchant using PIN code
#### HTTPMethod
POST
#### URI
/Redeem/goodsGroup/verify
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description | 
 | --- | --- | --- |--- |
| token | string | YES | access token returned from Auth | 
| goods_group | string | YES | goods group | 
| coupon_code | string | YES | coupon code of goods to verify | 
| pin_code | string | YES | merchant PIN Code generated from admin dashboard | 
#### Response
| Name | Type | Nullable | Description | Format| 
| --- | --- | --- | --- | --- |
#### Response Example
```json 

 ```
#### Error Response
| Name | Error Code | Message | 
 | --- | --- | --- |
## Redeem Sponsor
Redeem Sponsor Goods for a client’s website.
#### HTTPMethod
POST
#### URI
/Redeem/sponsor
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description | 
 | --- | --- | --- |--- |
| token | string | YES | access token returned from Auth | 
| goods_id | string | YES | goods id of goods store | 
| player_id | string | YES | player id as used in client's website | 
| amount | integer | YES | amount of the goods to give to player | 
#### Response
| Name | Type | Nullable | Description | Format| 
| --- | --- | --- | --- | --- |
#### Response Example
```json 

 ```
#### Error Response
| Name | Error Code | Message | 
 | --- | --- | --- |
## Redeem Sponsored Goods Group
Redeem Sponsored Goods given group.
#### HTTPMethod
POST
#### URI
/Redeem/sponsorGroup
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description | 
 | --- | --- | --- |--- |
| token | string | YES | access token returned from Auth | 
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
