# PHP-todo-app
# ✅ 自己署名 CA を使って HTTPS 通信する手順  
*（Windows + XAMPP + OpenSSL + C# フォームアプリ対応）*

---

## 🔧 ① OpenSSL で CA（認証局）と証明書のディレクトリ構成を作成

```bash
mkdir C:\xampp\myCA
cd C:\xampp\myCA
mkdir certs private newcerts
echo 1000 > serial
type nul > index.txt
```

---

## 📝 ② `openssl.cnf` の準備（CA 設定ファイル）

`C:\xampp\myCA\openssl.cnf` を作成して以下のように記述します：

```ini
[ ca ]
default_ca = CA_default

[ CA_default ]
dir             = C:/xampp/myCA
certs           = $dir/certs
crl_dir         = $dir/crl
database        = $dir/index.txt
new_certs_dir   = $dir/newcerts
certificate     = $dir/cacert.pem
serial          = $dir/serial
private_key     = $dir/private/cakey.pem
default_days    = 3650
default_md      = sha256
policy          = policy_anything

[ policy_anything ]
countryName             = optional
stateOrProvinceName     = optional
organizationName        = optional
organizationalUnitName  = optional
commonName              = supplied
emailAddress            = optional

[ req ]
default_bits       = 2048
default_md         = sha256
distinguished_name = req_distinguished_name
x509_extensions    = v3_ca
prompt             = yes
req_extensions = v3_req

[ req_distinguished_name ]
countryName                     = Country Name (2 letter code)
countryName_default             = JP
stateOrProvinceName             = State or Province Name (full name)
stateOrProvinceName_default     = Tokyo
localityName                    = Locality Name (eg, city)
localityName_default            = Shibuya
organizationName                = Organization Name (eg, company)
organizationName_default        = MyCompany
commonName                      = Common Name (e.g. server FQDN or YOUR name)
commonName_default              = MyRootCA

[ v3_ca ]
subjectKeyIdentifier=hash
authorityKeyIdentifier=keyid:always,issuer
basicConstraints = critical, CA:true
keyUsage = critical, digitalSignature, cRLSign, keyCertSign

[ v3_req ]
subjectAltName = @alt_names

[ alt_names ]
DNS.1 = localhost
IP.1  = 192.168.116.1
IP.2  = 192.168.192.1
```
**⚠ [ alt_names ]にhtpps接続させたい、ドメインやアドレスを記述** 

---

## 🔐 ③ 認証局（CA）の秘密鍵と証明書の作成

```bash
openssl genrsa -aes256 -out private\cakey.pem 4096
```
* `証明書`のパスワード2回入力 。**⚠パスワードは今後も使用するのでどこかに保存すること。**

```bash
openssl req -new -x509 -days 3650 -key private\cakey.pem -out cacert.pem -config openssl.cnf
```
色々と入力を求められますが、全部空白で問題ありません。
* `cacert.pem` は自作の **ルートCA証明書** です

---

## 🖥️ ④ サーバー証明書（localhost 用）の作成

```bash
openssl genrsa -out server.key 2048
```
* `server.key` が生成されます。
```bash
openssl req -new -key server.key -out server.csr -config openssl.cnf
```
**⚠ SAN に `localhost` や `192.168.116.1` が入っていることが重要です。**
* `server.csr` が生成されます。
```bash
openssl x509 -req -in server.csr -CA cacert.pem -CAkey private/cakey.pem -CAcreateserial -out server.crt -days 3650 -extensions v3_req -extfile openssl.cnf

```
* `server.crt` が生成されます。

---

## 🌐 ⑤ XAMPP に HTTPS を設定（例）
###  SSL設定を有効にする
httpd.confから下記のコードのコメントアウトを解除。

Include conf/extra/httpd-ssl.conf

### 証明書の設定
C:\xampp\apache\conf\に `ssl` フォルダを作成し、生成した証明書をコピーします。
* server.crt
* server.key
* cacert.pem

### `httpd-ssl.conf` の編集（例）

```apache
<VirtualHost _default_:443>
    DocumentRoot "C:/xampp/htdocs"
    ServerName localhost
    SSLEngine on
    SSLCertificateFile "conf/ssl/server.crt"
    SSLCertificateKeyFile "conf/ssl/server.key"
    SSLCACertificateFile "conf/ssl/cacert.pem"
</VirtualHost>
```
**⚠ServerName は　SANで設定した値**

**⚠ あくまで一例です。ファイルパスは任意に設定してください。**

✔ Apache を**再起動**してください

---

## 🧪 ⑥ ブラウザでアクセス確認

- URL: `https://localhost/`　**SANで設定した値**
- 初回は証明書警告が出ます（自己署名CAだから）
- Windows に CA を登録すれば警告も消えます（下記参照）
---
## 🎁 補足：Windows に CA を信頼させたい場合（任意）

### `certmgr.msc` を実行

1. 「信頼されたルート証明機関」→「証明書」→ インポート  
2. `cacert.pem` を選択  
3. ブラウザは閉じてから再度開く。

> Chrome / Edge でも証明書エラーが表示されなくなります

## 💻 ⑦ C# フォームアプリから HTTPS 通信するコード例

### 📜 PEM ファイルから CA を読み込む方法

```csharp
private X509Certificate2 LoadCaFromPem(string pemPath)
{
    var certPem = File.ReadAllText(pemPath);
    string base64 = certPem
        .Replace("-----BEGIN CERTIFICATE-----", "")
        .Replace("-----END CERTIFICATE-----", "")
        .Replace("\r", "")
        .Replace("\n", "");

    byte[] certBytes = Convert.FromBase64String(base64);
    return new X509Certificate2(certBytes);
}
```

### 🔒 サーバー証明書の検証ロジック

```csharp
private bool ValidateServerCertificate(HttpRequestMessage req, X509Certificate2 cert, X509Chain chain, SslPolicyErrors errors)
{
    chain.ChainPolicy.ExtraStore.Add(_trustedCa);
    chain.ChainPolicy.RevocationMode = X509RevocationMode.NoCheck;
    chain.ChainPolicy.VerificationFlags = X509VerificationFlags.AllowUnknownCertificateAuthority;

    bool isValid = chain.Build(cert);

    var root = chain.ChainElements[chain.ChainElements.Count - 1].Certificate;
    return isValid && root.Thumbprint == _trustedCa.Thumbprint;
}
```

> `_trustedCa` は `LoadCaFromPem()` で読み込んだ CA 証明書インスタンス

---

## ✅ 完成：アプリから HTTPS + 独自CA で検証付き通信

- Windows に CA を登録せずとも、コード内で検証を完結可能  
- XAMPP に HTTPS 対応サーバー構築完了  
- C# アプリから**CA検証付き通信が可能**

---


