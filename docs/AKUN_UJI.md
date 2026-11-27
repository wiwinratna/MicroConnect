# Test Accounts

Accounts made only for trying out the application. All data is fictitious.

**Password:** the value of `INITIAL_PASSWORD` in `.env` (never written here).
Skip the "change password" pop-up so the password stays the same.

| Login ID | Page | Business type | Level |
|---|---|---|---|
| `adminuji.id` | `/admin/login` | Admin | - |
| `barang1.id` | `/umkm/login` | Goods, processes materials | 1 |
| `barang2.id` | `/umkm/login` | Goods, processes materials | 2 |
| `barang3.id` | `/umkm/login` | Goods, processes materials | 3 |
| `dagang.id` | `/umkm/login` | Goods, no processing | 3 |
| `jasa.id` | `/umkm/login` | Service with stock | 3 |
| `jasanonstok.id` | `/umkm/login` | Service without stock | 3 |

Each UMKM has sample transactions for three months. Emails are `<username>@contoh.test`.
The production module is disabled for every account (MVP scope).

## Create or restore the accounts

```powershell
php artisan db:seed --class="Database\Seeders\AkunUji\AkunUjiSeeder"
```

Existing accounts are skipped, so running it again changes nothing. The seeder refuses
to run in production.
