<?php

function getConnection() {

	//-------------------
	//DBに接続
	//-------------------
	try {
		$dbinfo = parse_url(getenv('DATABASE_URL'));
		$dsn = 'pgsql:host=' . $dbinfo['host'] . ';dbname=' . substr($dbinfo['path'], 1);
		$pdo = new PDO($dsn, $dbinfo['user'], $dbinfo['pass']);
		} catch (PDOException $e) {
			exit('データベース接続失敗。'.$e->getMessage());
		}

	return $pdo;
}

//---------------------------------------------------
// SQLを実行する
//---------------------------------------------------
function execute( $conn, $sql, $param = array() ) {
	//-------------------
	//クエリのセット
	//-------------------
	$stmt = $conn->prepare( $sql );

	//-----------------------
	// バインド変数のセット
	//-----------------------
	foreach( $param as $key => $value ) {
		//$stmt->bindValue( 1, "aaa" );   // ?でbindするとき(1 origin)
		$stmt->bindValue( $key, $value );
	}

	try {
		//-------------------
		//クエリの実行
		//-------------------
		$stmt->execute();

	} catch (PDOException $e) {
		exit('データベース接続失敗。'.$e->getMessage());
	}
	return $stmt;
}


/**
 * データを追加する
 * @param string $name
 * @param int $rank
 * @param string $last_login
 * @param int $prof
 */
function insert_data ($name, $rank, $last_login, $prof) {
	$pdo = getConnection();
	try {
		$sql = "INSERT INTO `guild_member_rank`(`name`, `rank`, `last_login`, `prof`) ".
				"VALUES (:name , :rank, :last_login, :prof)";
		$stmt = $pdo -> prepare($sql);
		$stmt->bindParam(':name', $name, PDO::PARAM_STR);
		$stmt->bindParam(':rank', $rank, PDO::PARAM_INT);
		$stmt->bindParam(':last_login', $last_login, PDO::PARAM_STR);
		$stmt->bindParam(':prof', $prof, PDO::PARAM_INT);

		$stmt->execute();
	} catch (PDOException $e) {
		print('Error:'.$e->getMessage());
	}
}


/**
 * 登録されている全てのグラブルIDを返します
 * @return int[] グラブルID
 */
function get_member_prof_id () {
	$pdo = getConnection();
	try {
		$sql = "SELECT prof FROM `guild_member`";
		$stmt = $pdo->query($sql);
		$id = [];
		while ($row = $stmt -> fetch(PDO::FETCH_ASSOC)){
			$id[] = $row["prof"];
		}
	} catch (PDOException $e) {
		print('Error:'.$e->getMessage());
	}
	return $id;
}


/**
 * 指定したグラブルIDのRankなどをまとめて返します
 * @param int $prof
 * @return unknown[]
 */
function get_member_rank ($prof) {
	$pdo = getConnection();
	try {
		$sql = "SELECT * FROM `guild_member_rank` ".
				"WHERE `prof` = :prof ORDER BY time DESC";
		$stmt = $pdo -> prepare($sql);
		$stmt -> bindParam(':prof', $prof, PDO::PARAM_INT);
		$stmt -> execute();
		$detail = [];
		while ($row = $stmt -> fetch(PDO::FETCH_ASSOC)){
			$detail[] = $row;
		}
	} catch (PDOException $e) {
		print('Error:'.$e->getMessage());
	}
	return $detail;
}

/**
 * 指定したグラブルIDの基本情報を返します
 * @param int $prof
 * @return unknown[]
 */
function get_member_detail_from_prof ($prof) {
	$pdo = getConnection();
	try {
		$sql = "SELECT * FROM `guild_member` ".
				"WHERE `prof` = :prof";
		$stmt = $pdo -> prepare($sql);
		$stmt -> bindParam(':prof', $prof, PDO::PARAM_INT);

		$stmt -> execute();
		$detail;
		while ($row = $stmt -> fetch(PDO::FETCH_ASSOC)){
			$detail = $row;
		}
	} catch (PDOException $e) {
		print('Error:'.$e->getMessage());
	}
	return $detail;
}


/**
 * 指定した月の最初か最後のデータを取得します
 * @param int $month
 * @param int $asc
 * @return unknown[]
 */
function get_month_data ($prof, $month, $asc) {
	$pdo = getConnection();
	try {
		$sql = "SELECT * FROM `guild_member_rank` ".
			   "WHERE DATE_FORMAT(time, '%Y%m')=:month AND ".
			   "`prof` = :prof ORDER BY time ASC";
		$stmt = $pdo -> prepare($sql);
		$stmt -> bindParam(':prof', $prof, PDO::PARAM_INT);
		$stmt -> bindParam(':month', $month, PDO::PARAM_INT);
//		$stmt -> bindParam(':asc', $asc, PDO::PARAM_STR);
		$stmt -> execute();
		$detail = [];
		while ($row = $stmt -> fetch(PDO::FETCH_ASSOC)){
			$detail[] = $row;
		}
	} catch (PDOException $e) {
		print('Error:'.$e->getMessage());
	}
	return $detail;
}