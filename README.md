# Pimcore Social Data - WeChat Connector

[![Software License](https://img.shields.io/badge/license-GPLv3-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Latest Release](https://img.shields.io/packagist/v/dachcom-digital/social-data-wechat-connector.svg?style=flat-square)](https://packagist.org/packages/dachcom-digital/social-data-wechat-connector)
[![Tests](https://img.shields.io/github/actions/workflow/status/dachcom-digital/pimcore-social-data-wechat-connector/.github/workflows/codeception.yml?branch=master&style=flat-square&logo=github&label=codeception)](https://github.com/dachcom-digital/pimcore-social-data-wechat-connector/actions?query=workflow%3ACodeception+branch%3Amaster)
[![PhpStan](https://img.shields.io/github/actions/workflow/status/dachcom-digital/pimcore-social-data-wechat-connector/.github/workflows/php-stan.yml?branch=master&style=flat-square&logo=github&label=phpstan%20level%204)](https://github.com/dachcom-digital/pimcore-social-data-wechat-connector/actions?query=workflow%3A"PHP+Stan"+branch%3Amaster)

This Connector allows you to fetch social posts from WeChat. 

> [!CAUTION]  
> This Connector has reached its end of life and only receives compatibility update. It will not be developed further.

![Image](https://user-images.githubusercontent.com/7426193/97338536-c9c65c00-1881-11eb-9844-a6d83f3dba3f.png)

### Release Plan
| Release | Supported Pimcore Versions | Supported Symfony Versions | Release Date | Maintained     | Branch                                                                                  |
|---------|----------------------------|----------------------------|--------------|----------------|-----------------------------------------------------------------------------------------|
| **3.x** | `11.0`                     | `6.2`                      | 07.11.2023   | Feature Branch | master                                                                                  |
| **2.x** | `10.1` - `10.6`            | `5.4`                      | 05.01.2022   | Unsupported    | [2.x](https://github.com/dachcom-digital/pimcore-social-data-wechat-connector/tree/2.x) |
| **1.x** | `6.0` - `6.9`              | `3.4`, `^4.4`              | 22.10.2020   | Unsupported    | [1.x](https://github.com/dachcom-digital/pimcore-social-data-wechat-connector/tree/1.x) |

## Installation

```json
"require" : {
    "dachcom-digital/social-data" : "~3.0.0",
    "dachcom-digital/social-data-wechat-connector" : "~3.0.0"
}
```

Add Bundle to `bundles.php`:
```php
return [
    SocialData\Connector\WeChat\SocialDataWeChatConnectorBundle::class => ['all' => true],
];
```

### Install Assets
```bash
bin/console assets:install public --relative --symlink
```

## Enable Connector

```yaml
# config/packages/social_data.yaml
social_data:
    social_post_data_class: SocialPost
    available_connectors:
        -   connector_name: wechat
```

## Connector Configuration
![Image](https://user-images.githubusercontent.com/7426193/95994379-83f4a700-0e30-11eb-9aad-e85e3ff4853e.png)

Now head back to the backend (`System` => `Social Data` => `Connector Configuration`) and checkout the WeChat tab.
- Click on `Install`
- Click on `Enable`
- Before you hit the `Connect` button, you need to fill you out the Connector Configuration. After that, click "Save".
- Click `Connect`
  
## Connection
![image](https://user-images.githubusercontent.com/7426193/96002411-5e1fd000-0e39-11eb-9000-1f939cedf6af.png)

After hitting the "Connect" button, **a popup** will open and generate an access token.
If everything worked out fine, the connection setup is complete after the popup closes.
Otherwise, you'll receive an error message. You may then need to repeat the connection step.

## Feed Configuration

| Name    | Description                                                     |
|---------|-----------------------------------------------------------------|
| `Count` | Define a limit to restrict the amount of social posts to import |

## Third-Party Requirements
To use this connector, this bundle requires some additional packages:
- [overtrue/wechat](https://github.com/overtrue/wechat)

## License
**DACHCOM.DIGITAL AG**, Löwenhofstrasse 15, 9424 Rheineck, Schweiz  
[dachcom.com](https://www.dachcom.com), dcdi@dachcom.ch  
Copyright © 2024 DACHCOM.DIGITAL. All rights reserved.  

For licensing details please visit [LICENSE.md](LICENSE.md)  

## Upgrade Info
Before updating, please [check our upgrade notes!](UPGRADE.md)
