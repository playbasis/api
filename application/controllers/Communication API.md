# Communication API
## Send Email
Send email to a player.
#### HTTPMethod
POST
#### URI
/Email/send
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description | 
 | --- | --- | --- |--- |
| token | string | YES | access token returned from Auth | 
| player_id | string | YES | player id as used in client's website | 
| subject | string | YES | email subject | 
| message | string | YES | email message (either message or template_id is required) | 
| template_id | string | YES | template message (either message or template_id is required) | 
#### Response
| Name | Type | Nullable | Description | Format| 
| --- | --- | --- | --- | --- |
#### Response Example
```json 

 ```
#### Error Response
| Name | Error Code | Message | 
 | --- | --- | --- |
## Send Email Coupon
Send coupon to a player via email.
#### HTTPMethod
POST
#### URI
/Email/goods
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description | 
 | --- | --- | --- |--- |
| token | string | YES | access token returned from Auth | 
| player_id | string | YES | player id as used in client's website | 
| ref_id | string | YES | reference transaction id for redemption | 
| subject | string | YES | email subject | 
| message | string | YES | email message, can use variable {{coupon}} for the actual code | 
| template_id | string | YES | template message (either message or template_id is required) | 
#### Response
| Name | Type | Nullable | Description | Format| 
| --- | --- | --- | --- | --- |
#### Response Example
```json 

 ```
#### Error Response
| Name | Error Code | Message | 
 | --- | --- | --- |
## List Recent Email Sent to a Player
List recent email sent to a player.
#### HTTPMethod
GET
#### URI
/Email/recent
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description | 
 | --- | --- | --- |--- |
| player_id | string | YES | player id as used in client's website | 
| since | string | YES | 'datetime' format supported by strtotime | 
#### Response
| Name | Type | Nullable | Description | Format| 
| --- | --- | --- | --- | --- |
#### Response Example
```json 

 ```
#### Error Response
| Name | Error Code | Message | 
 | --- | --- | --- |
## List Email Template
List email template.
#### HTTPMethod
GET
#### URI
/Email/template
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
## Get Processed Email Template
Get processed email template.
#### HTTPMethod
GET
#### URI
/Email/template/:template_id
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description | 
 | --- | --- | --- |--- |
| template_id | string | YES | template message | 
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
## Send SMS
Send SMS to a player.
#### HTTPMethod
POST
#### URI
/Sms/send
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description | 
 | --- | --- | --- |--- |
| token | string | YES | access token returned from Auth | 
| player_id | string | YES | player id as used in client's website | 
| message | string | YES | SMS message (either message or template_id is required) | 
| template_id | string | YES | template message (either message or template_id is required) | 
#### Response
| Name | Type | Nullable | Description | Format| 
| --- | --- | --- | --- | --- |
#### Response Example
```json 

 ```
#### Error Response
| Name | Error Code | Message | 
 | --- | --- | --- |
## Send SMS Coupon
Send coupon to a player via SMS.
#### HTTPMethod
POST
#### URI
/Sms/goods
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description | 
 | --- | --- | --- |--- |
| token | string | YES | access token returned from Auth | 
| player_id | string | YES | player id as used in client's website | 
| ref_id | string | YES | reference transaction id for redemption | 
| message | string | YES | SMS message, can use variable {{coupon}} for the actual code | 
| template_id | string | YES | template message (either message or template_id is required) | 
#### Response
| Name | Type | Nullable | Description | Format| 
| --- | --- | --- | --- | --- |
#### Response Example
```json 

 ```
#### Error Response
| Name | Error Code | Message | 
 | --- | --- | --- |
## List Recent SMS Sent to a Player
List recent SMS sent to a player.
#### HTTPMethod
GET
#### URI
/Sms/recent
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description | 
 | --- | --- | --- |--- |
| player_id | string | YES | player id as used in client's website | 
| since | string | YES | 'datetime' format supported by strtotime | 
#### Response
| Name | Type | Nullable | Description | Format| 
| --- | --- | --- | --- | --- |
#### Response Example
```json 

 ```
#### Error Response
| Name | Error Code | Message | 
 | --- | --- | --- |
## List SMS Template
List SMS template.
#### HTTPMethod
GET
#### URI
/Sms/template
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
## Get Processed SMS Template
Get processed SMS template.
#### HTTPMethod
GET
#### URI
/Sms/template/:template_id
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description | 
 | --- | --- | --- |--- |
| template_id | string | YES | template message | 
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
## Device Registration
Register for your device 
#### HTTPMethod
POST
#### URI
/Push/deviceRegistration
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description | 
 | --- | --- | --- |--- |
| player_id | string | YES | player id | 
| token | string | YES | access token returned from Auth | 
| device_token | string | YES | access token returned from Device | 
| device_description | string | YES | Device model description | 
| device_name | string | YES | Device model name | 
| os_type | enumerated | YES | Choose os type | 
#### Response
| Name | Type | Nullable | Description | Format| 
| --- | --- | --- | --- | --- |
#### Response Example
```json 

 ```
#### Error Response
| Name | Error Code | Message | 
 | --- | --- | --- |
## Send Push-Notification
Send push notification to a player.
#### HTTPMethod
POST
#### URI
/Push/send
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description | 
 | --- | --- | --- |--- |
| token | string | YES | access token returned from Auth | 
| player_id | string | YES | player id as used in client's website | 
| message | string | YES | SMS message (either message or template_id is required) | 
| template_id | string | YES | template message (either message or template_id is required) | 
#### Response
| Name | Type | Nullable | Description | Format| 
| --- | --- | --- | --- | --- |
#### Response Example
```json 

 ```
#### Error Response
| Name | Error Code | Message | 
 | --- | --- | --- |
## Send Push-Notification Coupon
Send coupon to a player via push notification.
#### HTTPMethod
POST
#### URI
/Push/goods
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description | 
 | --- | --- | --- |--- |
| token | string | YES | access token returned from Auth | 
| player_id | string | YES | player id as used in client's website | 
| ref_id | string | YES | reference transaction id for redemption | 
| message | string | YES | SMS message, can use variable {{coupon}} for the actual code | 
| template_id | string | YES | template message (either message or template_id is required) | 
#### Response
| Name | Type | Nullable | Description | Format| 
| --- | --- | --- | --- | --- |
#### Response Example
```json 

 ```
#### Error Response
| Name | Error Code | Message | 
 | --- | --- | --- |
## List Recent Push Notification to a Player
List recent Push Notification to a player.
#### HTTPMethod
GET
#### URI
/Push/recent
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description | 
 | --- | --- | --- |--- |
| player_id | string | YES | player id as used in client's website | 
| since | string | YES | 'datetime' format supported by strtotime | 
#### Response
| Name | Type | Nullable | Description | Format| 
| --- | --- | --- | --- | --- |
#### Response Example
```json 

 ```
#### Error Response
| Name | Error Code | Message | 
 | --- | --- | --- |
## List Push Notification Template
List Push template.
#### HTTPMethod
GET
#### URI
/Push/template
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
## Get Processed Push Notification Template
Get processed Push Notification template.
#### HTTPMethod
GET
#### URI
/Push/template/:template_id
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description | 
 | --- | --- | --- |--- |
| template_id | string | YES | template message | 
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
