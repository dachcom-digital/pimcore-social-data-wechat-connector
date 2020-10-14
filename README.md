# Pimcore Social Data - WeChat Connector

This Connector allows you to fetch social posts from WeChat. 

![Image](https://user-images.githubusercontent.com/7426193/95995174-712ea200-0e31-11eb-88a0-933e622759f6.png)

#### Requirements
* [Pimcore Social Data Bundle](https://github.com/dachcom-digital/pimcore-social-data)

## Installation

### I. Add Dependencies
```json
"require" : {
    "dachcom-digital/social-data-wechat-connector" : "~1.0.0"
}
```

### II. Register Connector Bundle
```php
// src/AppKernel.php
use Pimcore\Kernel;
use Pimcore\HttpKernel\BundleCollection\BundleCollection;

class AppKernel extends Kernel
{
    public function registerBundlesToCollection(BundleCollection $collection)
    {
        $collection->addBundle(new SocialData\Connector\WeChat\SocialDataWeChatConnectorBundle());
    }
}
```

### III. Install Assets
```bash
bin/console assets:install web --relative --symlink
```

## Third-Party Requirements
To use this connector, this bundle requires some additional packages:
- [garbetjie/wechat-php](https://github.com/garbetjie/wechat-php)

## Enable Connector

```yaml
# app/config/config.yml
social_data:
    social_post_data_class: SocialPost
    available_connectors:
        -   connector_name: wechat
```

## Connector Configuration
![Image](https://user-images.githubusercontent.com/7426193/95994379-83f4a700-0e30-11eb-9aad-e85e3ff4853e.png)

Now head back to the backend (`System` => `Social Data` => `Connector Configuration`) and checkout the wechat tab.
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

| Name | Description
|------|----------------------|
| `Count` | Define a limit to restrict the amount of social posts to import |

## Copyright and license
Copyright: [DACHCOM.DIGITAL](http://dachcom-digital.ch)  
For licensing details please visit [LICENSE.md](LICENSE.md)  

## Upgrade Info
Before updating, please [check our upgrade notes!](UPGRADE.md)
