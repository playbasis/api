# Quiz API
## A List of Active Quizzes
Returns a list of active quizzes.
#### HTTPMethod
GET
#### URI
/Quiz/list
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
| --- | --- | --- |--- |
| player_id | string | YES | player id as used in client's website | 
| type | string | YES | quiz, poll | 
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
## Get Detail of a Quiz
Get detail of a quiz.
#### HTTPMethod
GET
#### URI
/Quiz/:id/detail
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
| --- | --- | --- |--- |
| id | string | YES | quiz id | 
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
## Random Get a Quiz for a Player
Randomly get one quiz of a list of active quizzes for a given player.
#### HTTPMethod
GET
#### URI
/Quiz/random/
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
| --- | --- | --- |--- |
| player_id | string | YES | player id as used in client's website | 
| type | string | YES | quiz, poll | 
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
## List Recent Quizzes Done by the Player
List recent quizzes done by the player.
#### HTTPMethod
GET
#### URI
/Quiz/player/:player_id/:limit
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
| --- | --- | --- |--- |
| player_id | string | YES | player id as used in client's website | 
| limit | string | YES | limit number of quizzes | 
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json 

 ```
#### Error Response
| Name | Error Code | Message |
| --- | --- | --- |
## List Pending Quizzes by the Player
List pending quizzes by the player.
#### HTTPMethod
GET
#### URI
/Quiz/player/:player_id/pending/:limit
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
| --- | --- | --- |--- |
| player_id | string | YES | player id as used in client's website | 
| limit | string | YES | limit number of quizzes | 
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json 

 ```
#### Error Response
| Name | Error Code | Message |
| --- | --- | --- |
## Get a Question from a Quiz
Get a question with a list of options for a given quiz.
#### HTTPMethod
GET
#### URI
/Quiz/:id/question
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
| --- | --- | --- |--- |
| id | string | YES | quiz id | 
| player_id | string | YES | player id as used in client's website | 
| random | enumerated | YES | 1=Random, 2=Not Random | 
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json 

 ```
#### Error Response
| Name | Error Code | Message |
| --- | --- | --- |
## Answer a Question from a Quiz
Submit a player's answer for a question for a given quiz.
#### HTTPMethod
POST
#### URI
/Quiz/:id/answer
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
| --- | --- | --- |--- |
| token | string | YES | access token returned from Auth | 
| id | string | YES | quiz id | 
| player_id | string | YES | player id as used in client's website | 
| question_id | string | YES | question id | 
| option_id | string | YES | option id | 
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json 

 ```
#### Error Response
| Name | Error Code | Message |
| --- | --- | --- |
## Rank Players by Their Scores for a Quiz
Rank players by their scores for a give quiz.
#### HTTPMethod
GET
#### URI
/Quiz/:id/rank/:limit
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
| --- | --- | --- |--- |
| id | string | YES | quiz id | 
| limit | string | YES | limit number of players | 
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json 

 ```
#### Error Response
| Name | Error Code | Message |
| --- | --- | --- |
## Query for Quiz's Statistics
Query a statistics of a quiz done by all players.
#### HTTPMethod
GET
#### URI
/Quiz/:id/stat
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
| --- | --- | --- |--- |
| id | string | YES | quiz id | 
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json 

 ```
#### Error Response
| Name | Error Code | Message |
| --- | --- | --- |
## Reset a Player's Quiz
Reset a quiz done by a player.
#### HTTPMethod
POST
#### URI
/Quiz/reset
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
| --- | --- | --- |--- |
| token | string | YES | access token returned from Auth | 
| player_id | string | YES | player id as used in client's website | 
| quiz_id | string | YES | quiz id | 
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json 

 ```
#### Error Response
| Name | Error Code | Message |
| --- | --- | --- |
