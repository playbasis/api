# Player API

## Player Info (public data only)
Get public information about a player
### HTTPMethod
GET
### URI
 /Player/:id
### RequiresOAuth
NO
### Parameters

Name | Type | Required | Description |
| --- | --- | --- | --- |
| id | `string` | YES | player id as used in client's website |

### Response
| Name | Type | Nullable | Description | Format |
| --- | --- | --- | --- |
| player | [player](#playerObject) | NO | JSON object of player |

### <a name="playerObject"></a>Response: player
Name | Type | Nullable | Description | Format |
| --- | --- | --- | --- |
| username | `string` | NO | player username as used in client's website |
| first_name | `string` | YES | player's first name |
| last_name | `string` | YES | player's last name |
| gender | `int` | NO | 0 = undefined, 1 = male, 2 = female |
| image | `url` | NO | player image, return as url |
| exp | `int` | NO | player experience point |
| level| `int` | NO | player id as used in client's website |
| date_added | `DateTime` | NO | Date that player was added | YYYY-MM-DDThh:mm:ssTZD |
| birth_date | `DateTime` | YES | player's birth date | YYYY-MM-DDThh:mm:ssTZD |
| last_login | `DateTime` | NO | Date that player last login | YYYY-MM-DDThh:mm:ssTZD |
| last_logout | `DateTime` | NO | Date that player last logout | YYYY-MM-DDThh:mm:ssTZD |
| cl_player_id | `string` | NO | player id as used in client's website |

### Response Example

```json
"player": {
  "image": "http://elasticbeanstalk-ap-southeast-1-007834438823.s3.amazonaws.com/user_content/thumb/3f1bda9cc3707a9ae53b0a4157ea8681.png",
  "username": "1",
  "exp": 3865,
  "level": 12,
  "first_name": "1",
  "last_name": "Sven",
  "gender": 0,
  "birth_date": null,
  "registered": "2016-01-22T12:35:16+0700",
  "last_login": "2016-05-11T15:11:01+0700",
  "last_logout": "2016-05-16T10:57:20+0700",
  "cl_player_id": "1"
}
```

### Error Response
| Name | Error Code | Message |
| --- | --- | --- |
| Invalid parameter |0903 | Invalid parameter id require , must not be blank or special |
| User doesn't exist |0200 | User doesn't exist |
