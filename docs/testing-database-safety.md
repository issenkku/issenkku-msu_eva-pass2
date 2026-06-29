# Testing Database Safety

Use the testing environment for all destructive database commands.

Safe commands:

```powershell
vendor\bin\pest
vendor\bin\pest tests\Feature\LoginFormTest.php
php artisan migrate:fresh --env=testing --force
```

Do not run destructive migration commands against the local `.env` database:

```powershell
php artisan migrate:fresh --force
```

The local `.env` database may point to the developer MySQL database. The testing environment uses `database/testing.sqlite`.
