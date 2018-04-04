# Behat Protocol
Provides steps to check the scheme used by HTTP resources

## Provides the following step definitions

```
Then the response should not contain internal http urls
Then I should not see any internal http urls in JavaScript
```

## Default behaviour

- `base_url` will be checked
- `X-Forwarded-Proto` header will be set to `https`

## Configure in behat.yml

Add `DennisDigital\Behat\Protocol\Context\ProtocolContext` under `Contexts`

#### You can also configure extra internal hosts to check

```
DennisDigital\Behat\Protocol\Context\ProtocolContext:
  parameters:
    hosts:
      - www.example.com
      - images.example.com
      - cdn.example.com
```

#### Specify extra headers to be sent with each request

```
DennisDigital\Behat\Protocol\Context\ProtocolContext:
  parameters:
    headers:
      "X-Forwarded-Proto": https
```

You can prevent the `X-Forwarded-Proto` being sent by setting it to `''`:
```
"X-Forwarded-Proto": ''
```
