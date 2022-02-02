#Testing with Cypress

As you can see the plugin is bundled with Cypress testing on this repository. You can use the tests, if you have some experience with testing.

***DO NOT USE IN PRODUCTION, THE TESTS MODIFY SETTINGS AND CREATE ORDERS***

## Requirements

* A hikashop installation is required, in which you need to have the sample theme installed and products displayed on the homepage.
* You need to have Paylike module installed and configured (**test keys** required)
* You also need to have a test client account with previous purchases and an admin account for which you set the credentials in the `cypress.env.json` file
* Lastly you need to have the some modules available on frontend
    - login form
    - hikashop currency switcher
    - hikashop cart

## Getting started

1. Run following commands into hikashop plugin folder (as is in this repo)

    ```bash
    npm install cypress --save-dev
    ```

2. Copy and rename `cypress.env.json.example` file in the root folder and fill the data as explained bellow:
```json
{
    "ENV_HTTP_AUTH_ENABLED": false, // if you have HTTP auth when accessing website
    "ENV_HTTP_USER": "", // if you have HTTP auth when accessing website
    "ENV_HTTP_PASS": "",
    "ENV_COOKIE_HASH": "", // name of the cookie (in Joomla it is a hash - it changes after each Joomla/Hikashop setup)
    "ENV_STORE_URL": "", // http(s)://baseUrl
    "ENV_ADMIN_URL": "", // like http(s)://baseUrl/administrator
    "ENV_CLIENT_USER": "", // frontend user
    "ENV_CLIENT_PASS": "",
    "ENV_ADMIN_USER": "", // admin user
    "ENV_ADMIN_PASS": "",
    "REMOTE_LOG_URL": "", // if you want to send log information about hikashop/paylike versions
    "ENV_CURRENCY_TO_CHANGE_WITH": "USD",
    "ENV_CAPTURE_MODE": "Delayed", // Instant/Delayed (write with capital first letter )
    "ENV_CHECKOUT_MODE": "before_order",
    "ENV_STOP_EMAIL": false, // if true => deactivate sending email on order creation / status change
    "ENV_LOG_VERSION": false, // if true => send hikashop / paylike modules versions remotely
    "ENV_SETTINGS_CHECK": false, // if true => change paylike capture mode as is specified in ENV_CAPTURE_MODE variable
    "ENV_CARD_NUMBER": 4100000000000000,
    "ENV_CARD_EXPIRY": 1226,
    "ENV_CARD_CVV": 654
}
```

3. Start the Cypress testing server.
    ```bash
    npx cypress open
    ```

4. Run
    ```bash
    ... # wait for cypress command
    ```
5. Run
    ```bash
    ... # wait for cypress command
    ```

## Getting Problems?

Since this is a frontend test, its not always consistent, due to delays or some glitches regarding overlapping elements. If you can't get over an issue please open an issue and we'll take a look.