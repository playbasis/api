# Store Organize API
## List organizations
List organizations as set from admin dashboard
#### HTTPMethod
get
#### URI
/StoreOrg/organizes
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
| --- | --- | --- |--- |
| id | string | YES | Specify Organize Id to retrieve | 
| search | string | YES | Specify organize name to search | 
| sort | string | YES | Specify field to be sorted [_id, *name*, status] | 
| order | string | YES | Specify sorted direction [desc, *asc*] | 
| offset | string | YES | Specify paging offset [default = 0] | 
| limit | string | YES | Specify paging limit [default = 20] | 
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json 

 ```
#### Error Response
| Name | Error Code | Message |
| --- | --- | --- |
## List nodes
List nodes as set from admin dashboard
#### HTTPMethod
get
#### URI
/StoreOrg/nodes
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
| --- | --- | --- |--- |
| id | string | YES | Specify Node Id to retrieve | 
| organize_id | string | YES | Specify Organize Id to retrieve | 
| parent_id | string | YES | Specify Parent Id to retrieve | 
| search | string | YES | Specify organize name to search | 
| sort | string | YES | Specify field to be sorted [_id, *name*, status] | 
| order | string | YES | Specify sorted direction [desc, *asc*] | 
| offset | string | YES | Specify paging offset [default = 0] | 
| limit | string | YES | Specify paging limit [default = 20] | 
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json 

 ```
#### Error Response
| Name | Error Code | Message |
| --- | --- | --- |
## Add player to Node
Add player to specific Node
#### HTTPMethod
POST
#### URI
/StoreOrg/nodes/:node_id/addPlayer/:player_id
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
| --- | --- | --- |--- |
| token | string | YES | access token returned from Auth | 
| node_id | string | YES | Node Id to add player | 
| player_id | string | YES | Player Id to add to Node | 
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json 

 ```
#### Error Response
| Name | Error Code | Message |
| --- | --- | --- |
## Remove player from Node
Remove Player from specific Node
#### HTTPMethod
POST
#### URI
/StoreOrg/nodes/:node_id/removePlayer/:player_id
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
| --- | --- | --- |--- |
| token | string | YES | access token returned from Auth | 
| node_id | string | YES | Node Id to add player | 
| player_id | string | YES | Player Id to add to Node | 
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json 

 ```
#### Error Response
| Name | Error Code | Message |
| --- | --- | --- |
## Set player role
Set player's organization role to specific Node
#### HTTPMethod
POST
#### URI
/StoreOrg/nodes/:node_id/setPlayerRole/:player_id
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
| --- | --- | --- |--- |
| token | string | YES | access token returned from Auth | 
| node_id | string | YES | Node Id to set player's role | 
| player_id | string | YES | Player Id to set player's role | 
| role | string | YES | Role name to set player's role | 
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json 

 ```
#### Error Response
| Name | Error Code | Message |
| --- | --- | --- |
## Unset player role
Unset player's organization role from specific Node
#### HTTPMethod
POST
#### URI
/StoreOrg/nodes/:node_id/unsetPlayerRole/:player_id
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
| --- | --- | --- |--- |
| token | string | YES | access token returned from Auth | 
| node_id | string | YES | Node Id to unset player's role | 
| player_id | string | YES | Player Id to unset player's role | 
| role | string | YES | Role name to unset player's role | 
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json 

 ```
#### Error Response
| Name | Error Code | Message |
| --- | --- | --- |
## Get Players list from node id
get player's list from specific Node
#### HTTPMethod
GET
#### URI
/StoreOrg/players/:node_id
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
| --- | --- | --- |--- |
| node_id | string | YES | Node Id to unset player's role | 
| role | string | YES | Role name to unset player's role | 
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json 

 ```
#### Error Response
| Name | Error Code | Message |
| --- | --- | --- |
## Peer Leader board
Returns leader board list for organization under given node_id
#### HTTPMethod
GET
#### URI
/StoreOrg/rankPeer/:node_id/:rank_by
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
| --- | --- | --- |--- |
| node_id | string | YES | organization id to be ranked | 
| rank_by | string | YES | name of point-based reward to rank players by | 
| page | number | YES | Select page to be reported, page 1 is the first page [default = first page] | 
| limit | integer | YES | number of results to return [default = 20] | 
| under_org | string | YES | true = return rank of organize under given node_id, false = return rank associate with given node_id | 
| role | String | YES | role to be filtered | 
| player_id | string | YES | player_id to return his/her own rank | 
| month | string | YES | month to rank players by (01, 02, 03,..., 12) | 
| year | string | YES | year to rank players by (2015, 2016 , ...) | 
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json 

 ```
#### Error Response
| Name | Error Code | Message |
| --- | --- | --- |
## Peer Leader board By Action
Returns leader board list for organization under given node_id
#### HTTPMethod
GET
#### URI
/StoreOrg/rankPeerByAccAction/:node_id/:action/:parameter
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
| --- | --- | --- |--- |
| node_id | string | YES | organization id to be ranked | 
| action | string | YES | name of action to rank players by | 
| parameter | string | YES | name of parameter of action to rank players by | 
| role | string | YES | role in organization to be filtered | 
| page | number | YES | Select page to be reported, page 1 is the first page [default = first page] | 
| limit | integer | YES | number of results to return [default = 20] | 
| player_id | string | YES | player id to return his/her own rank | 
| month | string | YES | month to rank players by (01, 02, 03,..., 12) | 
| year | string | YES | year to rank players by (2015, 2016 , ...) | 
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json 

 ```
#### Error Response
| Name | Error Code | Message |
| --- | --- | --- |
## Find child nodes
Find all child nodes under specific Node
#### HTTPMethod
GET
#### URI
/StoreOrg/nodes/:node_id/getChildNode/:layer
#### RequiresOAuth
YES
#### Parameters
| Name | Type | Required | Description |
| --- | --- | --- |--- |
| node_id | string | YES | Node Id that you want to find the child node | 
| layer | number | YES | Layer of nodes under specific node to find [default = 0 (for finding all layer)] | 
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json 

 ```
#### Error Response
| Name | Error Code | Message |
| --- | --- | --- |
## Sale report
Sale report of specific Node in a month
#### HTTPMethod
GET
#### URI
/StoreOrg/nodes/:node_id/saleReport
#### RequiresOAuth
YES
#### Parameters
| Name | Type | Required | Description |
| --- | --- | --- |--- |
| node_id | string | YES | Node Id that you want to find the child node | 
| month | string | YES | Select month to get sale report(ex. 1,2,..,12)[default = current month] | 
| year | string | YES | Select year to get sale report(ex. 2015)[default = current year] | 
| action | string | YES | Select action name to query from action log [default = "sell"] | 
| parameter | string | YES | Select parameter to report from action log [default = "amount"] | 
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json 

 ```
#### Error Response
| Name | Error Code | Message |
| --- | --- | --- |
## Sale history
Sale history of specific Node
#### HTTPMethod
GET
#### URI
/StoreOrg/nodes/:node_id/saleHistory/:count
#### RequiresOAuth
YES
#### Parameters
| Name | Type | Required | Description |
| --- | --- | --- |--- |
| node_id | string | YES | Node Id that you want to find the child node | 
| count | number | YES | Number of months to get the report | 
| month | string | YES | Select month to get sale report(ex. 1,2,..,12)[default = current month] | 
| year | string | YES | Select year to get sale report(ex. 2015)[default = current year] | 
| action | string | YES | Select action name to query from action log [default = "sell"] | 
| parameter | string | YES | Select parameter to report from action log [default = "amount"] | 
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json 

 ```
#### Error Response
| Name | Error Code | Message |
| --- | --- | --- |
## Sale board
Leader board by sale amount of all nodes under specific node
#### HTTPMethod
GET
#### URI
/StoreOrg/nodes/:node_id/saleBoard/:layer
#### RequiresOAuth
YES
#### Parameters
| Name | Type | Required | Description |
| --- | --- | --- |--- |
| node_id | string | YES | Node Id that you want to find the child node | 
| layer | number | YES | Layer of nodes under specific node to find [default = 0 (for finding all layer)] | 
| month | string | YES | Select month to get sale report(ex. 1,2,..,12)[default = current month] | 
| year | string | YES | Select year to get sale report(ex. 2015)[default = current year] | 
| action | string | YES | Select action name to query from action log [default = "sell"] | 
| parameter | string | YES | Select parameter to report from action log [default = "amount"] | 
| page | number | YES | Select page to be reported, page 1 is the first page [default = first page] | 
| limit | number | YES | limit per page to be reported [default = "20"] | 
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json 

 ```
#### Error Response
| Name | Error Code | Message |
| --- | --- | --- |
## Add Content to Node
Add Content to specific Node
#### HTTPMethod
POST
#### URI
/StoreOrg/nodes/:node_id/addContent/:content_id
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
| --- | --- | --- |--- |
| token | string | YES | access token returned from Auth | 
| node_id | string | YES | Node Id to add player | 
| content_id | string | YES | Content Id to add to Node | 
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json 

 ```
#### Error Response
| Name | Error Code | Message |
| --- | --- | --- |
## Remove Content from Node
Remove Content from specific Node
#### HTTPMethod
POST
#### URI
/StoreOrg/nodes/:node_id/removeContent/:content_id
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
| --- | --- | --- |--- |
| token | string | YES | access token returned from Auth | 
| node_id | string | YES | Node Id to add player | 
| content_id | string | YES | Content Id to add to Node | 
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json 

 ```
#### Error Response
| Name | Error Code | Message |
| --- | --- | --- |
## Set Content role
Set Content's organization role to specific Node
#### HTTPMethod
POST
#### URI
/StoreOrg/nodes/:node_id/setContentRole/:content_id
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
| --- | --- | --- |--- |
| token | string | YES | access token returned from Auth | 
| node_id | string | YES | Node Id to set player's role | 
| content_id | string | YES | Content Id to set content's role | 
| role | string | YES | Role name to set content's role | 
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json 

 ```
#### Error Response
| Name | Error Code | Message |
| --- | --- | --- |
## Unset Content role
Unset Content's organization role from specific Node
#### HTTPMethod
POST
#### URI
/StoreOrg/nodes/:node_id/unsetContentRole/:content_id
#### RequiresOAuth
NO
#### Parameters
| Name | Type | Required | Description |
| --- | --- | --- |--- |
| token | string | YES | access token returned from Auth | 
| node_id | string | YES | Node Id to unset player's role | 
| content_id | string | YES | Content Id to unset content's role | 
| role | string | YES | Role name to unset content's role | 
#### Response
| Name | Type | Nullable | Description | Format|
| --- | --- | --- | --- | --- |
#### Response Example
```json 

 ```
#### Error Response
| Name | Error Code | Message |
| --- | --- | --- |
