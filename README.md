# Behat Protocol
Provides steps to check the scheme use by HTTP resources

## Provides the following step definitions

```
Then the response should not contain internal http urls
Then I should not see any internal http urls in JavaScript
```

## Configure in behat.yml

Add `DennisDigital\Behat\Protocol\Context\ProtocolContext` under `Contexts`

By default, the `base_url` will be checked.

#### You can also configure extra internal hosts to check

```
DennisDigital\Behat\Protocol\Context\ProtocolContext:
  parameters:
    hosts:
      - www.example.com
      - images.example.com
      - cdn.example.com
```
