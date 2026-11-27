# Test Accounts

Accounts made only for trying out the application. All data is fictitious.

**Password:** the value of `INITIAL_PASSWORD` in `.env` (never written here).
Skip the "change password" pop-up so the password stays the same.

| Email (login) | Page | Business type | Level |
|---|---|---|---|
| `adminuji@contoh.test` | `/admin/login` | Admin | - |
| `barang1@contoh.test` | `/umkm/login` | Goods, processes materials | 1 |
| `barang2@contoh.test` | `/umkm/login` | Goods, processes materials | 2 |
| `barang3@contoh.test` | `/umkm/login` | Goods, processes materials | 3 |
| `dagang@contoh.test` | `/umkm/login` | Goods, no processing | 3 |
| `jasa@contoh.test` | `/umkm/login` | Service with stock | 3 |
| `jasanonstok@contoh.test` | `/umkm/login` | Service without stock | 3 |

Each UMKM has sample transactions for three months. The production module is disabled
for every account (MVP scope).

## Create or restore the accounts

```powershell
php artisan db:seed --class="Database\Seeders\AkunUji\AkunUjiSeeder"
```

Existing accounts are skipped, so running it again changes nothing. The seeder refuses
to run in production.
