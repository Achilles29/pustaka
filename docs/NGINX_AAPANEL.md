# Nginx aaPanel Deployment

Gunakan rewrite ini saat aplikasi berjalan di aaPanel dengan Nginx. File `.htaccess` hanya berlaku untuk Apache, sehingga Nginx harus diberi `try_files` sendiri.

## Jika domain langsung mengarah ke folder Pustaka

Root situs aaPanel menunjuk langsung ke folder aplikasi, misalnya:

```text
/www/wwwroot/pustaka
```

Isi rewrite:

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~* ^/(application|system|storage|db_backup|Buku|docs|sql|scripts|deploy)/ {
    deny all;
    return 404;
}

location ~ /\. {
    deny all;
    return 404;
}
```

## Jika aplikasi berada di subfolder `/pustaka`

Root situs aaPanel menunjuk ke folder induk, lalu aplikasi berada di `/pustaka`.

```nginx
location /pustaka/ {
    try_files $uri $uri/ /pustaka/index.php?$query_string;
}

location ~* ^/pustaka/(application|system|storage|db_backup|Buku|docs|sql|scripts|deploy)/ {
    deny all;
    return 404;
}

location ~ /\. {
    deny all;
    return 404;
}
```

## Catatan

- Jangan hanya mengandalkan `.htaccess` di server Nginx.
- Pastikan `index.php` tetap berada di root aplikasi.
- `application`, `system`, `storage`, `sql`, `docs`, dan `db_backup` tidak boleh bisa dibuka dari browser.
- Jika CSS/JS tidak muncul, cek apakah `base_url` sudah mengikuti path domain yang dipakai.
