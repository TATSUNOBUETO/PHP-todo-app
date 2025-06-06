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

`C:\xampp\myCA\openssl.cnf` を作成して以下のように記述します。

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
1. 秘密鍵の作成
```bash
openssl genrsa -aes256 -out private\cakey.pem 4096
```
証明書のパスワード2回入力 。
**⚠パスワードは今後も使用するのでどこかに保存すること。** 

2. 証明書の作成

```bash
openssl req -new -x509 -days 3650 -key private\cakey.pem -out cacert.pem -config openssl.cnf
```
色々と入力を求められますが、全部空白で問題ありません。

`cacert.pem` は自作の **ルートCA証明書** です

---

## 🖥️ ④ サーバー証明書の作成

1. 秘密鍵（server.key）の作成 
```bash
openssl genrsa -out server.key 2048
```
`server.key` が作成されます。

2. 証明書署名要求（server.csr）の作成 

```bash
openssl req -new -key server.key -out server.csr -config openssl.cnf
```
**⚠ SAN に `localhost` や `192.168.116.1` が入っていることが重要です。**

`server.csr` が作成されます。

3. サーバー証明書（server.crt）の作成（CA による署名）

```bash
openssl x509 -req -in server.csr -CA cacert.pem -CAkey private/cakey.pem -CAcreateserial -out server.crt -days 3650 -extensions v3_req -extfile openssl.cnf

```
`server.crt` が作成されます。

---

## 🌐 ⑤ XAMPP に HTTPS を設定（例）
1. ###  SSL設定を有効にする
httpd.confから下記のコードのコメントアウトを解除。

`Include conf/extra/httpd-ssl.conf`

2. ### 証明書の設定
C:\xampp\apache\conf\に `ssl` フォルダを作成し、生成した証明書をコピーします。
* server.crt
* server.key
* cacert.pem

3. ### `httpd-ssl.conf` の編集（例）

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
using System;
using System.Net.Http;
using System.Net.Security;
using System.Security.Cryptography.X509Certificates;
using System.IO;
using System.Windows.Forms;

namespace https_app
{
    public partial class Form1 : Form
    {
        private X509Certificate2 _trustedCa;
        public Form1()
        {
            InitializeComponent();

            // PEMファイルからCA証明書を読み込む（OpenSSL形式に対応）
            _trustedCa = LoadCaFromPem(@"C:\xampp\myCA\cacert.pem");
        }

        private  async void button1_Click(object sender, EventArgs e)
        {
            var handler = new HttpClientHandler();
            //クライアントのOSにルート証明書がなくても_trustedCaで検証させる。
            handler.ServerCertificateCustomValidationCallback = ValidateServerCertificate;

            using (var client = new HttpClient(handler))
            {
                try
                {
                    string result = await client.GetStringAsync("https://192.168.116.1/");
                    MessageBox.Show(result, "HTTPS Response");
                }
                catch (Exception ex)
                {
                    MessageBox.Show(ex.Message, "Error");
                }
            }
        }
        private bool ValidateServerCertificate(HttpRequestMessage req, X509Certificate2 cert, X509Chain chain, SslPolicyErrors errors)
        {
            // 自作 CA をチェーンに追加
            chain.ChainPolicy.ExtraStore.Add(_trustedCa);

            // 証明書失効チェックを無効化（ローカル用）
            chain.ChainPolicy.RevocationMode = X509RevocationMode.NoCheck;

            // 未知のルート（自己署名CA）を許可
            chain.ChainPolicy.VerificationFlags = X509VerificationFlags.AllowUnknownCertificateAuthority;

            // チェーンを構築
            bool isValid = chain.Build(cert);

            // チェーンの最上位（ルート）が自作CAと一致するか検証
            var root = chain.ChainElements[chain.ChainElements.Count - 1].Certificate;
            bool isTrusted = root.Thumbprint == _trustedCa.Thumbprint;

            return isValid && isTrusted;
        }
        private X509Certificate2 LoadCaFromPem(string pemPath)
        {
            var certPem = File.ReadAllText(pemPath);

            // PEM 形式から DER 形式に変換（Base64デコード）
            string base64 = certPem
                .Replace("-----BEGIN CERTIFICATE-----", "")
                .Replace("-----END CERTIFICATE-----", "")
                .Replace("\r", "")
                .Replace("\n", "");

            byte[] certBytes = Convert.FromBase64String(base64);
            return new X509Certificate2(certBytes);
        }
    }
}

```

> `_trustedCa` は `LoadCaFromPem()` で読み込んだ CA 証明書インスタンス

---

## ✅ 完成：アプリから HTTPS + 独自CA で検証付き通信

- Windows に CA を登録せずとも、コード内で検証を完結可能  
- XAMPP に HTTPS 対応サーバー構築完了  
- C# アプリから**CA検証付き通信が可能**

---


