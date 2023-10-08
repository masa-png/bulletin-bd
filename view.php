<?php

$current_date = null;
$pdo = null;
$message = array();
$message_array = array();
$success_message = null;
$error_message = array();
$escaped = array();
$statment = null;
$res = null;

//DBに接続
try {
    $pdo = new PDO('mysql:charset=UTF8;dbname=bulletin-bd;host=localhost', 'masayuki', 'g@Q72tG@Q9z');
} catch (PDOException $e) {
    $error_message[] = $e->getMessge();
}

if (!empty($_POST["button"])) {

    // 名前を入力したかどうか
    if (empty($_POST["username"])) {
        $error_message[] = "お名前を入力してください";
    } else {
        $escaped['username'] = htmlspecialchars($_POST["username"], ENT_QUOTES, "UTF-8");
    }

    // コメントを入力したかどうか
    if (empty($_POST["comment"])) {
        $error_message[] = "コメントを入力してください";
    } else {
        // 調べること
        $escaped['comment'] = htmlspecialchars($_POST["comment"], ENT_QUOTES, "UTF-8");
    }

    // エラーメッセージがない時-> データーを保存
    if (empty($error_message)) {
        $current_date = date("Y-m-d H:i:s");

        // トランザクション開始
        $pdo->beginTransaction();

        try {

            // DBに追加
            $statment = $pdo->prepare("INSERT INTO `bbd-table` (`username`, `comment`, `postDate`) VALUES (:username, :comment, :current_date)");
            // 値をセット
            $statment->bindParam(':username', $escaped["username"], PDO::PARAM_STR);
            $statment->bindParam(':comment', $escaped["comment"], PDO::PARAM_STR);
            $statment->bindParam(':current_date', $current_date, PDO::PARAM_STR);

            // SQLクエリの実行
            $res = $statment->execute();
            $res = $pdo->commit();
        } catch (Exception $e) {
            // エラーが発生した時は処理を取り消す
            $pdo->rollBack();
        }

        if ($res) {
            $success_message = "コメントを書き込みました";
        } else {
            $error_message[] = "書き込みに失敗しました";
        }

        $statment = null;
    }
}

// DBからコメントデーターを取得する
$sql = "SELECT `id`, `username`, `comment`, `postDate` FROM `bbd-table`;";
$message_array = $pdo->query($sql);

// DBを閉じる
$pdo = null;
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>掲示板</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1 class="title">掲示板</h1>
    <div class="boardWrapper">
        <!-- メッセージの送信に成功した時-->
        <?php if (!empty($success_message)) : ?>
            <p class="success_message"><?php echo $success_message; ?></p>
        <?php endif; ?>

        <!-- バリデーションチェック時-->
        <?php if (!empty($error_message)) : ?>
            <?php foreach ($error_message as $value) : ?>
                <div class="error_message">!<?php echo $value ?></div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- 投稿エリアー -->
        <section>
        <?php if (!empty($message_array)) : ?>
            <?php foreach ($message_array as $value) : ?>
            <article>
                <div class="wrapper">
                    <div class="nameArea">
                        <span>名前：</span>
                        <p class="username"><?php echo $value['username'] ?></p>
                        <time>:<?php echo date('Y/m/d H:i', strtotime($value['postDate'])); ?></time>
                    </div>
                    <p class="comment"><?php echo $value['comment'] ?></p>
                </div>
            </article>
            <?php endforeach; ?>
        <?php endif; ?>
        </section>

        <form method="POST" action="" class="formWrapper">
            <div>
                <input type="submit" value="投稿" name="button">
                <label for="usernameLabel">名前：</label>
                <input type="text" name="username">
            </div>
            <div>
                <textarea name="comment" class="commentTextArea"></textarea>
            </div>
        </form>
    </div>
</body>
</html>