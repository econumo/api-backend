# Econumo backend

### Currencies 

Econumo uses [openexchangerates.org](https://docs.openexchangerates.org/docs/api-introduction) for updating currencies and it's rates.
It's free for econumo needs.

Basically, if you don't plan to use multicurrency account - probably you don't need it to. 

#### How to update currencies list and it's rates?
1. Register new account at [openexchangerates.org](https://openexchangerates.org)
2. Generate new access token
3. Put token at `.env` as `OPEN_EXCHANGE_RATES_TOKEN={YOUR_TOKEN}`
4. Run  `bin/console app:update-currencies` to update full list of available currencies
5. Run `bin/console app:update-currency-rates` to update currency rates

If you would like to change base currency - put at `.env` string `CURRENCY_BASE=USD`