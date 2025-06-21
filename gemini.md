承知いたしました。
C\# Windows Formsを用いた大規模プロジェクトをチームで開発するにあたり、ご提示いただいた要件に基づいた「教育資料」と「コーディング統制資料」をマークダウン形式で作成します。
Windows Formsの経験が浅いメンバーにも理解しやすいよう、概念の説明から具体的な実装方法までを網羅的に記述します。

-----

# Windows Forms 大規模プロジェクト開発ガイドライン

## 1\. 教育資料

### 1.1. はじめに - なぜ新しいアーキテクチャが必要か？

本プロジェクトでは、複数のアプリケーションを一つに統合し、長期的にメンテナンスしやすく、機能追加にも柔軟に対応できるソフトウェアを目指します。

従来のWindows Forms開発（フォームに直接コードを書く方法）は、小規模なツールでは手軽ですが、プロジェクトが大規模化・複雑化するにつれて、以下のような問題が発生しがちです。

  * **コードがスパゲッティ化する**: 1つのファイルに様々な処理が混在し、どこで何をしているのか追うのが困難になる。
  * **修正が困難で、影響範囲が広い**: 1つの修正が、予期せぬ別の機能に影響を及ぼす（デグレード）。
  * **再利用性が低い**: ロジックが画面と密結合しているため、他の画面で同じような機能を使いたくても流用できない。
  * **テストが困難**: 自動テストの導入が難しく、手動での確認作業に多くの時間がかかる。

これらの問題を解決するため、本プロジェクトでは以下の設計思想・パターンを導入します。

  * **MVC (Model-View-Controller)**: コードの役割を分離し、見通しを良くする。
  * **データオリエンテッド**: アプリケーションの状態を一元管理し、シンプルに扱う。
  * **SOLID原則**: 変更に強く、柔軟で、再利用性の高い部品（クラス）を作るための設計原則。

これらの学習コストは決して低くありませんが、一度身につければ大規模開発を効率的に進めるための強力な武器となります。チーム全員で協力し、高品質なアプリケーションを構築していきましょう。

### 1.2. MVC (Model-View-Controller) パターン

MVCは、アプリケーションの機能を「**Model**」「**View**」「**Controller**」の3つの役割に分割する設計パターンです。

| 要素         | 役割                                                                                                                              | 具体例 (Windows Forms)                                    |
| :----------- | :-------------------------------------------------------------------------------------------------------------------------------- | :-------------------------------------------------------- |
| **Model** | **データとビジネスロジック**\<br\>・アプリケーションが扱うデータそのもの。\<br\>・データの加工、計算、検証などのルール。\<br\>・サーバーAPIとの通信処理。 | `UserModel`, `ProductModel`, API通信を行うサービスクラス      |
| **View** | **画面表示とユーザー入力**\<br\>・ユーザーに見える部分（UI）。\<br\>・コントロールの配置やデザイン。\<br\>・ユーザーからの操作（クリック、入力など）を受け付ける。**ロジックは持たない**。 | `Form`クラス, `UserControl`クラス                           |
| **Controller** | **ViewとModelの仲介役**\<br\>・Viewからのユーザー操作を受け取り、Modelに処理を依頼する。\<br\>・Modelの状態が変化したら、それをViewに反映させる。\<br\>・画面遷移の制御。 | `LoginController`, `ProductController` といった専用のクラス |

**【重要】** これまでの「フォームのボタンクリックイベントに直接処理を書く」スタイルから、「**ViewはControllerに通知するだけ、実際の処理はControllerとModelが行う**」というスタイルに思考を転換することが最も重要です。

### 1.3. データオリエンテッドなアプローチ

アプリケーション全体で共有する必要がある情報（状態）を、一つのオブジェクトに集約して管理する考え方です。

本プロジェクトでは、`AppModel` というクラスがその役割を担います。

```csharp
// AppModel.cs (共通モデル)
public class AppModel
{
    // ログインしたユーザー情報
    public UserInfo CurrentUser { get; set; }

    // ユーザーが持つ権限情報
    public AuthorizationInfo Authorizations { get; set; }

    // アプリケーションの状態 (例: サーバーとの接続状態など)
    public AppStatus Status { get; set; }
}
```

**メリット:**

  * **状態管理の集約**: 「ログインしているか？」「何の権限を持っているか？」といった情報が`AppModel`を見れば一目瞭然になります。
  * **データの一貫性**: アプリケーションのどこからでも同じ情報にアクセスできるため、情報の食い違いが発生しにくくなります。
  * **動的なUI構築**: `AppModel`が保持する権限情報をもとに、利用できるメニュー項目やボタンを動的に生成・表示/非表示にすることが容易になります。

### 1.4. SOLID原則 - 変更に強いクラスを作るために

SOLIDは、オブジェクト指向設計における5つの重要な原則の頭文字をとったものです。すべてを厳密に適用するのは難しいですが、意識することでコードの質が格段に向上します。

特に本プロジェクトでは、以下の2つを強く意識してください。

1.  **S: 単一責任の原則 (Single Responsibility Principle)**

      * **「クラスを変更する理由は、1つでなければならない」**
      * MVCパターンそのものが、この原則に基づいています。Viewは「表示」、Controllerは「制御」、Modelは「データ」というように、各クラスが持つ責任を一つに絞ります。
      * 例: API通信、データ計算、ファイル操作などのロジックを、すべてControllerに書くのではなく、それぞれ専用のサービスクラスに分離します。

2.  **D: 依存性逆転の原則 (Dependency Inversion Principle)**

      * **「具体的な実装に依存せず、抽象（インターフェース）に依存する」**
      * これは「**依存性の注入 (Dependency Injection - DI)**」というテクニックで実現します。
      * **悪い例 👎**: Controllerが、内部でViewクラスを`new`して生成する。
        ```csharp
        public class ProductController
        {
            private ProductView _view;
            public ProductController()
            {
                // Controllerが具象クラスであるProductViewを直接知ってしまっている (密結合)
                this._view = new ProductView();
            }
        }
        ```
      * **良い例 👍**: Controllerは、外部から作られたViewのインスタンスをコンストラクタで受け取る（注入される）。
        ```csharp
        public class ProductController
        {
            private readonly IProductView _view; // 具象ではなくインターフェースに依存
            private readonly IProductModel _model;

            // コンストラクタで、必要なインスタンスを外部から受け取る (DI)
            public ProductController(IProductView view, IProductModel model)
            {
                this._view = view;
                this._model = model;
            }
        }
        ```
      * **メリット**:
          * クラス間の結合度が下がり、修正や交換が容易になる。
          * テスト時に、本物のViewやModelの代わりに「モック」と呼ばれる偽物を注入できるため、テストが非常にしやすくなる。

-----

## 2\. コーディング統制資料

### 2.1. 基本方針

1.  **Strict MVC**: すべての画面は必ずModel, View, Controllerに分割する。
2.  **View is Dumb**: View（Form/UserControl）のコードビハインド (`.cs`) には、UIの振る舞いを決定するロジックを記述しない。
3.  **DI First**: クラス間の依存関係は、原則としてコンストラクタによる依存性の注入（DI）で解決する。`new`キーワードによる具象クラスの生成は、アプリケーションの起動時や画面遷移の起点など、限定的な箇所でのみ許可する。
4.  **Interface-based Programming**: 主要なクラス（特にViewとModel）にはインターフェースを定義し、Controllerは具象クラスではなくインターフェースに依存する。

### 2.2. ディレクトリ構造

プロジェクトのルートディレクトリは、以下のように構成します。

```
/SolutionRoot
|-- /ProjectName.sln
|-- /ProjectName
|   |-- Program.cs
|   |
|   |-- /Common                   # アプリケーション全体で共通の要素
|   |   |-- /Models               # (例: AppModel.cs, UserInfo.cs)
|   |   |-- /Views                # (例: MainForm.cs, HeaderControl.cs)
|   |   |-- /Controllers          # (例: AppController.cs, NavigationController.cs)
|   |   |-- /Services             # (例: ApiClient.cs, AuthService.cs)
|   |   |-- /Interfaces           # 共通インターフェース
|   |
|   |-- /Screens                  # 各画面のモジュール
|   |   |-- /Login                # 画面ID: Login
|   |   |   |-- LoginController.cs
|   |   |   |-- LoginView.cs
|   |   |   |-- ILoginView.cs
|   |   |
|   |   |-- /InventoryTop         # 画面ID: 在庫トップ
|   |   |   |-- /Models           # この画面固有のモデル
|   |   |   |-- /Views            # この画面固有のUserControlなど
|   |   |   |-- /Controllers      # この画面固有のロジック
|   |   |   |-- InventoryTopController.cs
|   |   |   |-- InventoryTopView.cs   # メインパネルに表示するUserControl
|   |   |   |-- IInventoryTopView.cs
|   |   |
|   |   |-- /HRTop                # 画面ID: 人事トップ
|   |   |   |-- ...
|   |
|   |-- /Resources                # 画像などのリソースファイル
```

### 2.3. 命名規則

| 対象       | 規約                                         | 例                                          |
| :--------- | :------------------------------------------- | :------------------------------------------ |
| **クラス** | `UpperCamelCase`                             | `ProductController`, `MainForm`             |
| **インターフェース** | `I` + `UpperCamelCase`                       | `IProductView`, `IApiService`               |
| **メソッド** | `UpperCamelCase`                             | `LoadProducts`, `UpdateHeader`              |
| **プロパティ** | `UpperCamelCase`                             | `ProductName`, `StockQuantity`              |
| **プライベート変数** | `_` + `lowerCamelCase`                       | `_productService`, `_currentUser`           |
| **ファイル名** | クラス名・インターフェース名と一致させる     | `ProductController.cs`, `IProductView.cs`   |
| **画面フォルダ** | 画面の役割を示す `UpperCamelCase` の画面ID | `Login`, `ProductList`, `UserDetail`        |
| **コントロール** | 機能 + 種類（`lowerCamelCase`）             | `userNameTextBox`, `loginButton`, `productGridView` |

### 2.4. 実装ガイドライン

#### 2.4.1. Viewの実装 (Form / UserControl)

Viewの責務は「表示」と「入力の受付」のみです。

  * **インターフェースを実装する**: Controllerとの疎結合を保つため、必ずインターフェースを定義し、実装します。
  * **プロパティを公開する**: ControllerがViewの表示を操作したり、入力値を取得したりするために、コントロールと連動するプロパティをインターフェース経由で公開します。
  * **イベントは`Action`や`Func`で公開する**: ボタンクリックなどのイベントは、Controllerが処理を注入できるように`Action`型のプロパティとして公開します。

**【例】`IProductView.cs`**

```csharp
public interface IProductView
{
    // Controllerから設定/取得するためのプロパティ
    string ProductName { get; set; }
    string Quantity { get; set; }
    object DataSource { set; } // DataGridView用

    // Controllerに処理を注入させるためのイベントハンドラ
    event Action SearchEvent;
    event Action AddEvent;

    // ControllerからViewを直接操作するためのメソッド
    void ShowMessage(string message);
}
```

**【例】`ProductView.cs` (コードビハインド)**

```csharp
public partial class ProductView : UserControl, IProductView
{
    public ProductView()
    {
        InitializeComponent();

        // Viewのイベントをインターフェースのイベントに紐づける
        this.searchButton.Click += (s, e) => SearchEvent?.Invoke();
        this.addButton.Click += (s, e) => AddEvent?.Invoke();
    }

    // --- IProductViewの実装 ---

    public string ProductName
    {
        get => productNameTextBox.Text;
        set => productNameTextBox.Text = value;
    }

    public string Quantity
    {
        get => quantityTextBox.Text;
        set => quantityTextBox.Text = value;
    }

    public object DataSource
    {
        set => productDataGridView.DataSource = value;
    }

    public event Action SearchEvent;
    public event Action AddEvent;

    public void ShowMessage(string message)
    {
        // ここではフッターへの通知を想定 (実際にはMainForm経由で行う)
        MessageBox.Show(message);
    }
}
```

**※注意**: コードビハインドには、`if`文による条件分岐や計算などのビジネスロジックは一切含めません。

#### 2.4.2. Controllerの実装

Controllerは、ViewとModelを結びつけ、アプリケーションのロジックを制御します。

  * **コンストラクタで依存を注入する**: 担当するViewや、利用するModel/Serviceをコンストラクタで受け取ります。
  * **Viewのイベントを購読する**: コンストラクタで、Viewが公開するイベントに処理メソッドを登録します。
  * **ビジネスロジックはModel/Serviceに委譲する**: Controller自身が複雑な計算やAPI通信を行うのではなく、それらを担当するModelやServiceクラスのメソッドを呼び出します。

**【例】`ProductController.cs`**

```csharp
public class ProductController
{
    private readonly IProductView _view;
    private readonly IProductModel _model;
    private readonly IApiService _apiService; // API通信担当サービス

    public ProductController(IProductView view, IProductModel model, IApiService apiService)
    {
        // 依存を注入
        _view = view;
        _model = model;
        _apiService = apiService;

        // Viewのイベントに処理を紐づけ
        _view.SearchEvent += OnSearch;
        _view.AddEvent += OnAdd;
    }

    // 検索ボタンが押されたときの処理
    private async void OnSearch()
    {
        try
        {
            var products = await _apiService.GetProductsAsync(_view.ProductName);
            _model.Products = products; // Modelを更新
            _view.DataSource = _model.Products; // ModelのデータでViewを更新
        }
        catch (Exception ex)
        {
            _view.ShowMessage($"エラーが発生しました: {ex.Message}");
        }
    }

    // 追加ボタンが押されたときの処理
    private void OnAdd()
    {
        // 新規登録画面への遷移処理などを、画面遷移担当のControllerに依頼する
        // NavigationController.NavigateTo("ProductDetail_New");
    }
}
```

#### 2.4.3. 画面遷移と動的生成

画面遷移は、特定のController（例: `NavigationController`）が一元管理します。

1.  ユーザーがメニューをクリックすると、`MainController`がイベントを受け取ります。
2.  `MainController`は、クリックされたメニューに対応する画面ID（例: `InventoryTop`）を`NavigationController`に渡します。
3.  `NavigationController`は、画面IDに基づいて、その画面に必要なM, V, Cのインスタンスを生成します。
      * `InventoryTopView`を`new`する。
      * `InventoryTopModel`を`new`する。
      * `InventoryTopController`を`new`し、コンストラクタにViewとModelのインスタンスを渡す。
4.  生成されたView（UserControl）を`MainForm`のメインパネルに配置し、表示します。
5.  `MainForm`のヘッダーやフッターの操作も、各画面のControllerから`MainController`の公開メソッドを通じて行います。

**【例】`NavigationController.cs` (概念コード)**

```csharp
public class NavigationController
{
    private readonly Panel _mainPanel; // MainFormのメインパネル
    private readonly IServiceProvider _serviceProvider; // DIコンテナなど

    public NavigationController(Panel mainPanel, IServiceProvider serviceProvider)
    {
        _mainPanel = mainPanel;
        _serviceProvider = serviceProvider;
    }

    public void NavigateTo(string screenId)
    {
        _mainPanel.Controls.Clear();
        UserControl view = null;

        switch (screenId)
        {
            case "InventoryTop":
                // DIコンテナがあれば、依存関係を自動で解決してくれる
                // view = _serviceProvider.GetService<InventoryTopView>();
                
                // 手動でやる場合
                var inventoryView = new InventoryTopView();
                var inventoryModel = new InventoryModel();
                var apiService = new ApiService(); // 本来はDIで取得
                // Controllerを生成することで、ViewとModelが紐づく
                new InventoryTopController(inventoryView, inventoryModel, apiService); 
                view = inventoryView;
                break;

            case "HRTop":
                // ...
                break;
        }

        if (view != null)
        {
            view.Dock = DockStyle.Fill;
            _mainPanel.Controls.Add(view);
        }
    }
}
```

-----

このガイドラインが、チーム開発を円滑に進める一助となれば幸いです。不明点があれば、都度チームで議論し、本ドキュメントを更新していくことが重要です。
