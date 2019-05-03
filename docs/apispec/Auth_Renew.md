`Category` Auth 

`API` Renew  

`Method` post 

`Url` https://api.pbapp.net/Auth/renew?iodocs=true


CURL test script


```
#! /bin/bash
curl -data "{\"success\":\"Value\"\"response\":\"Value\"\"error_code\":\"Value\"\"message\":\"Value\"\"timestamp\":\"Value\"\"time\":\"Value\"\"version\":\"Value\"]}" -H "Content-Type: application/json" "https://api.pbapp.net/Auth/renew?iodocs=true&api_key=VALUE&api_secret=VALUE" | json_pp 
```

In Parameters

	`string` api_key
	`string` api_secret

Out Parameters

 	`boolean` success
 	`string` response
 	`string` error_code
 	`string` message
 	`number` timestamp
 	`string` time
 	`string` version

OpenAPI spec


```
 "paths": {
        "https://api.pbapp.net/Auth/renew?iodocs=true": {
            "post": {
                "tags": ["Auth"],
                "parameters": [
                	{
                		"in": "query",
                        "name": "api_key",
            			"type": "string"
            		},
                	{
                		"in": "query",
                        "name": "api_secret",
            			"type": "string"
            		},
                ],
                "responses": {
        			"200": {          
          				"content": {
            				"application/json": {
              					"schema": {
                    				"$ref": "#/definitions/RenewResponse"
                    		}
                		}
                	}
                }
            }
},
"definitions": {
    "RenewResponse": {
		"type": "object",
		"properties": {  
			"success": {
            	"type": "boolean"
            },
			"response": {
            	"type": "string"
            },
			"error_code": {
            	"type": "string"
            },
			"message": {
            	"type": "string"
            },
			"timestamp": {
            	"type": "number"
            },
			"time": {
            	"type": "string"
            },
			"version": {
            	"type": "string"
            },
          }
    }
}
```


PHP Annotation


```
/**
 * @SWG\Post (
 *     path="/https://api.pbapp.net/Auth/renew?iodocs=true",
 *     description="undefined",
 *     @SWG\Parameter(
 *         name="api_key",
 *         in=query,
 *         type="string",
 *         description=api key issued by Playbasis,
 *         required=true,
 *     ),
 *     @SWG\Parameter(
 *         name="api_secret",
 *         in=query,
 *         type="string",
 *         description=api secret issued by Playbasis,
 *         required=true,
 *     ),
 *     @SWG\Response(
 *         response=200,
 *         description="OK",
 *     )
 * )
 */

```

