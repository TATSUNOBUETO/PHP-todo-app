承知いたしました。
C\# Windows Formsを用いた大規模プロジェクトをチームで成功させるための教育資料とコーディング規約を作成します。
チームメンバーの皆様が同じ認識を持ち、一貫性のある高品質なコードを記述できるよう、背景や目的から具体例までを網羅した内容とします。

-----

## C\# Windows Forms 大規模プロジェクト チーム開発ガイドライン

### はじめに

本ドキュメントは、SOA（サービス指向アーキテクチャ）に基づき、複数のアプリケーションを統合した新しいWindows Formsアプリケーションを開発するための教育資料兼コーディング規約です。

本プロジェクトの成功には、チーム全員がこれから導入する設計思想（データオリエンテッド、MVC、SOLID原則）を理解し、一貫したルールに則って開発を進めることが不可欠です。

従来の開発スタイルから脱却し、**保守性が高く、変更に強く、テストしやすい**アプリケーションを構築することを目指します。

-----

### 第1部：教育資料 - 新しい設計思想の理解

このセクションでは、プロジェクトで採用する主要な概念と言葉の定義、そしてなぜそれらが必要なのかを学びます。

#### 1\. 用語の定義（共通言語の確立）

円滑なコミュニケーションのために、まず以下の言葉の定義を統一します。

| 用語 | 本プロジェクトにおける定義 |
| :--- | :--- |
| **UI (User Interface)** | FormやUserControlなど、ユーザーが直接目にし、操作する画面要素全般を指します。本プロジェクトでは **View** の役割を担います。 |
| **MVC** | **Model-View-Controller** の略。アプリケーションを「データ(Model)」「画面(View)」「ロジック(Controller)」の3つの役割に分割する設計パターンです。 |
| **データオリエンテッド** | 「データ」を中心に設計する考え方。アプリケーションの状態（誰がログインしているか等）を特定の場所に集約して管理します。 |
| **SOLID原則** | オブジェクト指向設計における5つの重要な原則の頭文字をとったもの。凝集度を高め、結合度を低く保つための指針であり、変更に強い柔軟なソフトウェアを実現します。 |
| **DI (Dependency Injection)** | **依存性の注入**。クラスが必要とする別のクラス（依存オブジェクト）を、自身で生成するのではなく、外部から与えてもらう仕組みです。これにより、クラス間の結合度を下げることができます。 |
| **サービス** | 在庫サービス、人事サービス、給与サービスといった、特定のビジネス領域に対応する機能群を指します。 |

#### 2\. なぜ新しいアーキテクチャを導入するのか？

これまでの開発スタイルを見直し、新しいアーキテクチャを導入するのには明確な理由があります。

**これまでの開発（デザイナー主導、Formクラスへの直書き）の課題**

1.  **密結合（Fat Form / God Class）**:

      * **問題点**: 画面のイベントハンドラ（例: `button1_Click`）に、画面の見た目の変更、ビジネスロジック、データアクセスなど、あらゆる処理を詰め込んでしまいがちでした。これにより、一つのFormクラスが数百〜数千行にも及ぶ「神クラス」となり、コードが非常に見通しにくくなります。
      * **結果**: どこを修正すればよいか分からず、修正による副作用（デグレ）が発生しやすくなります。

2.  **再利用性の欠如**:

      * **問題点**: ある画面で作成したロジックが、その画面（Form）と強く結びついているため、別の画面で似たような処理が必要になっても、コードの再利用が困難でした。
      * **結果**: 似たようなコードがプロジェクト内に散在し、仕様変更の際に修正漏れが発生する原因となります。

3.  **テストの困難さ**:

      * **問題点**: ビジネスロジックがUIコンポーネントと密接に絡み合っているため、ロジック単体での自動テストが非常に困難です。テストするには、実際に画面を操作する必要がありました。
      * **結果**: テストに時間がかかり、品質担保が個人のスキルに依存してしまいます。

4.  **保守性の低下と属人化**:

      * **問題点**: コードの構造が複雑化し、作成した本人でなければ理解・修正が難しくなります。
      * **結果**: 特定の担当者がいないとメンテナンスできなくなり、開発スピードが低下します。

**新しいアーキテクチャ（MVC + SOLID）がもたらすメリット**

これらの問題を解決するのが、MVCによる「**関心の分離**」とSOLID原則です。

  * **保守性の向上**: View（見た目）、Controller（ロジック）、Model（データ）の役割が明確に分かれているため、コードの見通しが良くなります。仕様変更の際、修正すべき箇所を特定しやすくなります。
  * **再利用性の向上**: ビジネスロジックがUIから独立しているため、別のUIでも同じロジックを再利用できます。
  * **テストの容易性**: UIと切り離されたControllerやModelは、単体テストが容易になります。これにより、ロジックの品質を自動で担保できます。
  * **チーム開発の効率化**: 役割分担が明確なため、UI担当とロジック担当で並行して作業を進めやすくなります。

#### 3\. プロジェクトにおける設計思想の適用範囲

本プロジェクトでは、思想を次のように使い分けます。

  * **データオリエンテッドの適用範囲**:

      * **アプリケーション起動 〜 ログイン 〜 メイン画面表示まで**
      * 具体的には、アプリケーション全体の状態を管理する `AppModel` クラスが中心となります。ここにはログインしたユーザー情報、保有権限、選択中のテーマなどが格納されます。
      * `LoginController` が認証に成功したら `AppModel` を更新し、`MainController` は `AppModel` に格納された権限情報をもとに、サイドパネルのメニューを動的に生成します。
      * **ここまでは、アプリケーション全体の「状態（データ）」を起点にUIが決定されます。**

  * **MVC + SOLID原則の適用範囲**:

      * **メイン画面表示後 〜 各業務画面の操作**
      * ユーザーがメニューをクリックし、各業務画面（在庫管理、人事情報など）が表示されてからの実装は、すべてMVCとSOLID原則に基づきます。
      * `MainController` が画面遷移を管理し、遷移先の画面に対応する `View`, `Controller`, `Model` を生成します。この際、DIの考え方を用いて、ControllerにViewや必要な依存クラスを注入します。
      * **ここからは、各画面が疎結合なコンポーネントとして独立して動作します。**

-----

### 第2部：コーディング規約

このセクションでは、具体的な実装ルールを定めます。チーム全員がこの規約を守ることで、コードの一貫性と品質を保ちます。

#### 1\. ディレクトリ構造

プロジェクトのルートディレクトリは以下のように構成します。各画面は、一意の「画面ID」を持つフォルダで管理します。

```
/（ソリューションルート）
├─ Program.cs
├─ App.config
├─ Models/           (共通モデル: AppModelなど)
├─ Views/            (共通View: メインフォーム(ShellView)など)
├─ Controllers/      (共通Controller: MainControllerなど)
├─ Services/         (APIクライアントなどの共通サービス)
├─ Commons/          (共通のヘルパークラスなど)
├─ Screens/          (各画面を格納するルートフォルダ)
│  ├─ SC001_Login/   (画面ID_画面名)
│  │  ├─ LoginView.cs (.cs, .Designer.cs, .resx)
│  │  ├─ LoginController.cs
│  │  └─ LoginModel.cs
│  ├─ SC101_InventoryList/
│  │  ├─ InventoryListView.cs
│  │  ├─ InventoryListController.cs
│  │  └─ InventoryListModel.cs
│  └─ ... (他の画面フォルダ)
```

#### 2\. 命名規則

| 対象 | 命名規則 | 例 |
| :--- | :--- | :--- |
| **Form / UserControl** | `[画面内容]View` | `InventoryListView`, `LoginView` |
| **Controller** | `[画面内容]Controller` | `InventoryListController` |
| **Model** | `[画面内容]Model` | `InventoryListModel` |
| **インターフェース** | `I` + 名前 | `IInventoryService`, `IView` |
| **プライベート変数** | `_` + キャメルケース | `_inventoryService` |

#### 3\. MVCの責務分離（最重要）

各コンポーネントの役割を厳密に守ってください。

##### **View (Form / UserControl)**

  * **責務**:
      * コントロールの配置とデザイン。（静的なものはデザイナー、動的なものはControllerからの指示で）
      * ユーザーからの入力（クリック、テキスト入力など）を受け付ける。
      * Controllerから渡されたデータを画面に表示する。
      * Controllerを操作するためのプロパティやメソッドを公開する。
  * **禁止事項**:
      * **ビジネスロジックの記述（IF文での分岐処理、計算など）**
      * **APIやデータベースへの直接アクセス**
      * **ファイルI/O**
      * **画面遷移のロジック**
      * **ControllerやModelを`new`で直接生成すること**

**【Bad Practice】**

```csharp
// LoginView.cs (悪い例)
private void loginButton_Click(object sender, EventArgs e)
{
    // View内でAPI通信とロジックを記述してしまっている
    var api = new AuthApi();
    var result = api.Login(userIdTextBox.Text, passwordTextBox.Text);

    if (result.IsSuccess)
    {
        MessageBox.Show("ログイン成功");
        var mainForm = new MainForm(); // 画面遷移もここにある
        mainForm.Show();
        this.ParentForm.Close();
    }
    else
    {
        MessageBox.Show("ログイン失敗");
    }
}
```

**【Good Practice】**

```csharp
// ILoginView.cs (Viewのインターフェース: テスト容易性のために推奨)
public interface ILoginView
{
    string UserId { get; }
    string Password { get; }
    void ShowLoginError(string message);
}

// LoginView.cs (UserControl)
public partial class LoginView : UserControl, ILoginView
{
    private LoginController _controller;

    // Controllerは外部から注入される
    public void SetController(LoginController controller)
    {
        _controller = controller;
    }

    public string UserId => userIdTextBox.Text;
    public string Password => passwordTextBox.Text;

    private void loginButton_Click(object sender, EventArgs e)
    {
        // Controllerに処理を依頼するだけ
        _controller.Login();
    }

    public void ShowLoginError(string message)
    {
        MessageBox.Show(message, "エラー", MessageBoxButtons.OK, MessageBoxIcon.Error);
    }
}
```

##### **Controller**

  * **責務**:
      * ViewとModelの間の調整役。
      * Viewからのイベントを受け取り、対応するビジネスロジック（またはサービスクラスのメソッド）を呼び出す。
      * ビジネスロジックの実行結果をModelに反映し、Viewに画面更新を依頼する。
      * 画面遷移の制御（MainControllerの責務）。
      * 依存クラスの生成とViewへのインジェクション。
  * **禁止事項**:
      * `System.Windows.Forms`名前空間のコントロール（Button, TextBoxなど）を直接参照・操作すること。（`using System.Windows.Forms;` を極力書かない）
      * `MessageBox.Show()`などのUI固有の処理を直接呼び出すこと。（Viewにメソッドを定義して呼び出す）

**【Good Practice】**

```csharp
// LoginController.cs
public class LoginController
{
    private readonly ILoginView _view;
    private readonly IAuthService _authService;
    private readonly AppModel _appModel;

    // DI: コンストラクタで依存オブジェクトを受け取る
    public LoginController(ILoginView view, IAuthService authService, AppModel appModel)
    {
        _view = view;
        _authService = authService;
        _appModel = appModel;
    }

    public async void Login()
    {
        try
        {
            // Model(AppModel)に状態をセット
            var user = await _authService.AuthenticateAsync(_view.UserId, _view.Password);
            _appModel.CurrentUser = user;

            // 画面遷移はMainControllerなどに通知して行わせる
            // OnLoginSuccess?.Invoke(this, EventArgs.Empty);
        }
        catch (AuthenticationException ex)
        {
            // Viewにエラー表示を依頼
            _view.ShowLoginError(ex.Message);
        }
    }
}
```

##### **Model**

  * **責務**:
      * アプリケーションのデータと状態を保持する。
      * 例：APIから取得したデータのリスト、ユーザーが入力中のデータ、画面の表示状態など。
      * 必要であれば、プロパティの変更を通知する仕組み（`INotifyPropertyChanged`）を実装する。
  * **禁止事項**:
      * **UIに関する情報を持つこと（Viewへの参照など）**
      * **Controllerへの参照を持つこと**

**【Good Practice】**

```csharp
// AppModel.cs (データオリエンテッドの中核)
public class AppModel : INotifyPropertyChanged
{
    private UserModel _currentUser;

    public UserModel CurrentUser
    {
        get => _currentUser;
        set
        {
            _currentUser = value;
            OnPropertyChanged(nameof(CurrentUser));
            OnPropertyChanged(nameof(IsAuthenticated));
        }
    }

    public bool IsAuthenticated => CurrentUser != null;

    // INotifyPropertyChangedの実装...
    public event PropertyChangedEventHandler PropertyChanged;
    protected virtual void OnPropertyChanged(string propertyName)
    {
        PropertyChanged?.Invoke(this, new PropertyChangedEventArgs(propertyName));
    }
}

// UserModel.cs
public class UserModel
{
    public string UserId { get; set; }
    public string UserName { get; set; }
    public string[] Permissions { get; set; } // ["在庫サービス:管理者", "人事サービス:一般"]
}
```

#### 4\. 画面遷移の実装

画面遷移は、`MainController` のような中央集権的なコントローラが一元管理します。

1.  ユーザーがサイドメニューの項目をクリックします。
2.  `MainView` はクリックイベントを `MainController` に通知します。その際、メニューに紐づく「画面ID」を渡します。
3.  `MainController` は、受け取った画面IDを基に、対応する `View`, `Controller`, `Model` をインスタンス化します。
4.  `MainController` は、生成した `Controller` に、`View` や必要なサービスクラスを注入（DI）します。
5.  最後に、生成した `View`（UserControl）をメインパネルに表示します。

<!-- end list -->

```csharp
// MainController.cs (抜粋)
public class MainController
{
    private readonly IMainView _mainView;
    private readonly AppModel _appModel;

    public MainController(IMainView mainView, AppModel appModel)
    {
        _mainView = mainView;
        _appModel = appModel;
        _mainView.MenuSelected += OnMenuSelected; // イベント購読
    }

    // メニューが選択されたときの処理
    private void OnMenuSelected(object sender, MenuEventArgs e)
    {
        // 現在表示されているパネルをクリア
        _mainView.ClearMainPanel();

        // 画面IDに基づいて遷移先を決定 (DIコンテナを使うとよりクリーンになる)
        UserControl viewToShow = null;
        switch (e.ScreenId)
        {
            case "SC101":
                var inventoryView = new InventoryListView();
                var inventoryModel = new InventoryListModel();
                var inventoryController = new InventoryListController(inventoryView, inventoryModel, _appModel);
                inventoryView.SetController(inventoryController); // ViewにControllerをセット
                viewToShow = inventoryView;
                break;
            // 他の画面...
        }

        if (viewToShow != null)
        {
            _mainView.ShowInMainPanel(viewToShow);
        }
    }
}
```

#### 5\. 共通パネル（ヘッダー・フッター）の操作

各業務画面からヘッダーの画面名やフッターのステータスを操作する必要がありますが、これもViewが直接触るべきではありません。

1.  `MainView` (メインフォーム) に、ヘッダーやフッターを更新するための `public` メソッドを定義します。
      * `public void SetHeaderTitle(string title)`
      * `public void SetFooterStatus(string message)`
2.  各画面の `Controller` は、`MainView` のインスタンス（またはインターフェース）への参照を保持します。
3.  `Controller` は、処理の状況に応じて `MainView` のメソッドを呼び出し、共通パネルの表示を更新させます。

<!-- end list -->

```csharp
// InventoryListController.cs (抜粋)
public class InventoryListController
{
    private readonly IMainView _mainView; // メインViewの参照

    public InventoryListController(IInventoryListView view, ..., IMainView mainView)
    {
        _view = view;
        _mainView = mainView;
    }

    public async void LoadInventories()
    {
        _mainView.SetHeaderTitle("在庫一覧"); // ヘッダーを更新
        _mainView.SetFooterStatus("在庫データを取得中..."); // フッターを更新
        
        var data = await _inventoryService.GetAllAsync();
        _view.DisplayInventories(data);

        _mainView.SetFooterStatus("取得完了");
    }
}
```

-----

以上が、本プロジェクトを進める上での基本的なガイドラインとなります。
初めは窮屈に感じるかもしれませんが、このルールを守ることが、将来の自分たちを助け、プロジェクトを成功に導く鍵となります。

不明な点があれば、遠慮なく質問し、チーム内で認識を合わせていきましょう。
