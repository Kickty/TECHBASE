<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>mission_5</title>
</head>

<body>

    <?php

        //データベース接続
        $dsn = "データベース名";
        $user = "ユーザー名";
        $password = "パスワード";
        //エラー表示設定
        $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

        //テーブル作成
        $sql = "CREATE TABLE IF NOT EXISTS MyDB"
        ." ("
        . "id INT AUTO_INCREMENT PRIMARY KEY,"
        . "name char(32),"
        . "comment TEXT,"
        . "timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,"
        . "password TEXT"
        .");";
        $stmt = $pdo -> query($sql);
    ?>

    <?php
        $error_nopass = "パスワードを入力してください";
        $error_wrongpass = "投稿が存在しないか、またはパスワードが一致しません";

        //入力用フォームの処理
        if(!(empty($_POST["name"]) || empty($_POST["comment"]))){

            //書き込む文字列の生成
            $name_post = $_POST["name"];
            $comment_post = $_POST["comment"];
            $password_post = $_POST["password"];

            //コメント編集時の処理
            if(!empty($_POST["flag"])){
                $str_disp = "コメントを編集しました";

                //レコード編集
                $id = $_POST["flag"];
                $sql = "UPDATE MyDB SET name=:name, comment=:comment WHERE id=:id";
                $stmt = $pdo -> prepare($sql);
                $stmt -> bindParam(":name", $name_post, PDO::PARAM_STR);
                $stmt -> bindParam(":comment", $comment_post, PDO::PARAM_STR);
                $stmt -> bindParam(":id", $id, PDO::PARAM_INT);
                $stmt -> execute();
            }

            //コメント新規投稿時の処理
            elseif(!empty($_POST["password"])){
                $str_disp = "新規コメントを受付けました";

                //レコード入力
                $sql = "INSERT INTO MyDB (name, comment, password) VALUES (:name, :comment, :password)";
                $stmt = $pdo -> prepare($sql);
                $stmt -> bindParam(":name", $name_post, PDO::PARAM_STR);
                $stmt -> bindParam(":comment", $comment_post, PDO::PARAM_STR);
                $stmt -> bindParam(":password", $password_post, PDO::PARAM_STR);
                $stmt -> execute();
            }
            else{
                $str_disp = $error_nopass;
            }
        }

        //削除用フォームの処理
        elseif(!(empty($_POST["num_del"]) || empty($_POST["pass_del"]))){
            $num_del = $_POST["num_del"];
            $pass_del = $_POST["pass_del"];
            $str_disp = $error_wrongpass;

            //番号が一致する投稿を取得
            $sql = "SELECT * FROM MyDB where id=:id_del";
            $stmt = $pdo -> prepare($sql);
            $stmt -> bindParam(":id_del", $num_del, PDO::PARAM_INT);
            $stmt -> execute();
            $result = $stmt -> fetch();

            if($pass_del == $result["password"]){
                $str_disp = "コメントを削除しました";

                //レコードの削除
                $sql = "DELETE FROM MyDB where id=:id_del";
                $stmt = $pdo -> prepare($sql);
                $stmt -> bindParam(":id_del", $num_del, PDO::PARAM_INT);
                $stmt -> execute();
            }
        }

        //編集用フォームの処理
        elseif(!(empty($_POST["num_edit"]) || empty($_POST["pass_edit"]))){
            $num_edit = $_POST["num_edit"];
            $pass_edit = $_POST["pass_edit"];
            $str_disp = $error_wrongpass;

            //投稿番号とパスワードを照合して入力用フォームに表示
            $sql = "SELECT * FROM MyDB where id=:id_edit";
            $stmt = $pdo -> prepare($sql);
            $stmt -> bindParam(":id_edit", $num_edit, PDO::PARAM_INT);
            $stmt -> execute();
            $result = $stmt -> fetch();

            if($pass_edit == $result["password"]){
                $str_disp = "コメントが見つかりました<br>投稿用フォームから編集して送信してください";
                $id_disp_form = $result["id"];
                $name_disp_form = $result["name"];
                $comment_disp_form = $result["comment"];
            }
            
        }
        else{
            $str_disp = "フォームに必要な情報を入れて送信してください";
        }

        //DB表示
        $sql = "SELECT * FROM MyDB";
        $stmt = $pdo -> query($sql);
        $results = $stmt -> fetchAll();
        foreach ($results as $row){
            //$rowの中にはテーブルのカラム名が入る
            echo $row["id"].", ";
            echo $row["name"].", ";
            echo $row["comment"].", ";
            echo $row["timestamp"]."<br>";
            echo "<hr>";
        }

        /*
        //テーブルの削除、諸々バグった時に
        $sql = "DROP TABLE MyDB";
        $stmt = $pdo -> query($sql);
        */

    ?>

    <p><!--入力用フォーム-->
        <form action="" method="post">
            コメントを投稿する<br>
            <input type="hidden" name="flag" value="<?php echo $id_disp_form ?>">
            <input type="text" name="name" placeholder="名前" value="<?php echo $name_disp_form ?>">
            <input type="text" name="comment" placeholder="コメント" value="<?php echo $comment_disp_form ?>">
            <br>
            <input type="text" name="password" placeholder="削除・編集用パスワード">
            <input type="submit" value="送信">
        </form>
    </p>

    <p><!--削除用フォーム-->
        <form action="" method="post">
            コメントを削除したい場合はコチラ<br>
            <input type="number" name="num_del" placeholder="削除する投稿の番号">
            <input type="text" name="pass_del" placeholder="パスワード">
            <input type="submit" value="削除">
        </form>
    </p>

    <p><!--編集用フォーム-->
        <form action="" method="post">
            コメントを編集したい場合はコチラ<br>
            <input type="number" name="num_edit" placeholder="編集する投稿の番号">
            <input type="text" name="pass_edit" placeholder="パスワード">
            <input type="submit" value="編集">
        </form>
    </p>

    <?php
        echo $str_disp;
    ?>

</body>
</html>