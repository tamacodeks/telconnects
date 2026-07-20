# Authentication
## How to?

DEMAT PRO uses token based authentication to access the API. You can grab your access token by registering in our portal.

DEMAT PRO expects for the API token to be included in all API requests to the server in request header that looks like the following:


```http
Authorization: Bearer LQBtT676H8BVB8kqsZJvy9eFiyPSDdzFQW0rCCGXJ
```

!> You must replace `LQBtT676H8BVB8kqsZJvy9eFiyPSDdzFQW0rCCGXJ` with your personal API key.

## Example


```php
$ch = curl_init();
$headers = array(
    'Accept: application/json',
    'Authorization: Bearer LQBtT676H8BVB8kqsZJvy9eFiyPSDdzFQW0rCCGXJ',
);
curl_setopt($ch, CURLOPT_URL, "api_endpoint_here");
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

//changeable GET,POST,POST,DELETE
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
//IF REQUEST METHOD ONLY POST or POST
//curl_setopt($ch, CURLOPT_POSTFIELDS,$body);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
```

## Response

### Success
```json
{
    "data": {
        "" : ""
    }
}
```

### Error
```json
{
    "error": "Unauthenticated!"
}
```

