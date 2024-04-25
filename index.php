<?php

/**
 * @since 2017/05/16
 * @author ThinhHV <thinh@thinhhv.com>
 * @description Boom Online
 * @copyright (c) 2023 ThinhHV Platform
 */

ob_start();
@session_start();
error_reporting(0);
$ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['REMOTE_ADDR'];
$root = '';

//-------------------------//
//----------CONFIG---------//
//-------------------------//
// column/row matrix
$arrMatrix = array(
  0 => 5,
  1 => 7,
  2 => 10,
  3 => 15,
  4 => 20,
  5 => 0, # custom
);
// rand by %percent / BOOM rate
// higher is easier
$arrLevel = array(
  0 => 80,
  1 => 70,
  2 => 65,
  3 => 60,
);

//-------------------------//
//-------END CONFIG--------//
//-------------------------//

if (isset($_POST['id']) && isset($_POST['lv']) && isset($_POST['tp']) && $_POST['id'] == "REGISTER") {
  //-------------------------//
  //---------REGISTER--------//
  //-------------------------//
  if (
    isset($_SESSION['lv'])
    && isset($_SESSION['tp'])
    && $_SESSION['lv'] == $_POST['lv']
    && $_SESSION['tp'] == $_POST['tp']
    && (
      $_POST['tp'] != 5
      || ($_SESSION['matrix-x'] == $_POST['matrix-x'] && $_SESSION['matrix-y'] == $_POST['matrix-y'])
    )
  ) {
    $_SESSION['c'] = 1;
  } else {
    $_SESSION['c'] = 0;
  }
  $_SESSION['id'] = $ip;
  $_SESSION['lv'] = $_POST['lv'];
  $_SESSION['tp'] = $_POST['tp'];
  $_SESSION['matrix-x'] = ((int) $_POST['matrix-x']) ?? 1;
  $_SESSION['matrix-y'] = ((int) $_POST['matrix-y']) ?? 1;
} else if (isset($_POST['id']) && $_POST['id'] == "LOGOUT") {
  //-------------------------//
  //---------LOGOUT----------//
  //-------------------------//
  unset($_SESSION['id']);
  Header('Location: ' . $root . 'index.php');
}

//-------------------------//
//--------BODY HTML--------//
//-------------------------//
echo <<<EOL
<!DOCUMENT html>
<html>
  <head>
    <title>Boom Online - Code by ThinhHV</title>
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, minimum-scale=1.0">
    <style>*{font-family:monospace;font-size:16px}table{border-top:1px solid #ddd;border-right:1px solid #ddd;max-width:300px;margin-bottom:5px;transform:translate3d(0, 0, 0);}td{border:1px solid #ddd;text-align:center;margin:0;border-top:0;border-right:0;min-width:30px;}.check{background:#fdd;}.die{background:#fcc;}red{color:red;}.green{color:green;}.form-matrix{overflow-x:hidden;}.table-matrix{overflow-x:auto;}.matrix-custom{display:flex;align-items:center;justify-content:center;gap:5px;margin:10px}.boom{color:red}</style>
  </head>
  <body>
    <center>
EOL;

if (!isset($_SESSION['id'])) {
  //-------------------------//
  //-----IF NOT LOGGED-------//
  //-------------------------//
  $_lv = array('', '', '', '');
  $_tp = array('', '', '', '', '', '');
  $_x = $_SESSION['matrix-x'] || '';
  $_y = $_SESSION['matrix-y'] || '';
  if (isset($_SESSION['lv']) && isset($_SESSION['tp'])) {
    $_lv[$_SESSION['lv']] = ' checked';
    $_tp[$_SESSION['tp']] = ' checked';
  } else {
    $_lv[0] = ' checked';
    $_tp[0] = ' checked';
  }
  echo <<<EOL
      BOOM Online / Setup to play:
      <br/>
      <form action="{$root}index.php" method="post">
        Level:
        <br/>
        <input type="radio" name="lv" value="0"$_lv[0]>Easy
        <input type="radio" name="lv" value="1"$_lv[1]>Normal
        <input type="radio" name="lv" value="2"$_lv[2]>Hard
        <input type="radio" name="lv" value="3"$_lv[3]>Very Hard

        <br/>--------------<br/>
        <input type="radio" name="tp" value="0"$_tp[0]>5x5
        <input type="radio" name="tp" value="1"$_tp[1]>7x7
        <input type="radio" name="tp" value="2"$_tp[2]>10x10
        <input type="radio" name="tp" value="3"$_tp[3]>15x15
        <input type="radio" name="tp" value="4"$_tp[4]>20x20

        <div class="matrix-custom">
          <input type="radio" name="tp" value="5"$_tp[5]>
          <input type="number" min="1" max="100" re name="matrix-x" value="$_x" placeholder="x">
          x
          <input type="number" min="1" max="100" name="matrix-y" value="$_y" placeholder="y">
        </div>

        <input type="submit" name="id" value="REGISTER"> 
        <input type="submit" name="id" value="RELOAD">
      </form>
EOL;
} else {
  $id = strtoupper(substr(md5($_SESSION['id']), 0, 10));
  echo 'BOOM Online / ID: ' . $id . '<br/>';

  //-------------------------//
  //-------SETUP DATA--------//
  //-------------------------//

  // SETUP
  // Path to file DATA
  $file = 'data/' . $id;
  $tp = $_SESSION['tp'];
  $lv = $_SESSION['lv'];
  // SETUP LEVEL
  $nx = $arrMatrix[$tp] ?? $arrMatrix[0];
  $ny = $nx;
  if ($tp == 5) {
    $nx = $_SESSION['matrix-x'];
    $ny = $_SESSION['matrix-y'];
  }
  // SETUP n% BOOM
  $nn = $arrLevel[$lv] ?? $arrLevel[0];

  // CREATE ARRAY AND SAVE DATA
  if (
    // init when no code
    !isset($_SESSION['code'])
    || (
      isset($_POST['id'])
      // init when reset or register
      && ($_POST['id'] == 'RESET' || $_POST['id'] == 'REGISTER')
      && (
        // && when flag
        $_SESSION['c'] == 0
        || ($_POST['id'] == 'RESET' && $_SESSION['c'] == 1)
      )
    )
  ) {
    $t = array();
    $b = array();
    $d = 0;
    for ($i = 0; $i < $nx; $i++) {
      for ($j = 0; $j < $ny; $j++) {
        // create t array
        $m = mt_rand(0, 100);
        // is_boom: 1, not_boom: 0
        (int) $t[$i][$j] = $m > $nn ? 1 : 0;
        // Count '0' (not boom) in array
        if ($m <= $nn)
          $d++;
      }
    }
    $data = serialize($t);
    $_SESSION['code'] = $data;
    // SAVE DATA ARRAY
    file_put_contents($file, $data);
    // CREATE PROGRESS
    file_put_contents($file . '.progress', $data);
    // CREATE TURN + COUNT '0' IN ARRAY
    file_put_contents($file . '.m', (($lv == 2 && $tp > 1) || ($lv == 3 && $tp == 2) ? '3' : ($lv == 3 && $tp > 2 ? '5' : '2')) . '.' . $d . '.0');
  } else if (isset($_POST['_x']) && isset($_POST['_id']) && isset($_POST['act'])) {
    // FUNC POST, SAVE DATA WHEN PLAY
    $_id = $_POST['_id'];
    $h = explode('.', $_id);
    $h0 = $h[0];
    $h1 = $h[1];
    $act = $_POST['act'];
    if (is_numeric(str_replace('.', '', $_id)) && $h0 < $nx && $h1 < $ny && $h0 > -1 && $h1 > -1) {
      $o = unserialize(file_get_contents($file . '.progress'));
      if ($act == 'CHECK' && $o[$h0][$h1] != 3) {
        // IF POST IS CHECK
        $o[$h0][$h1] = 2;
        file_put_contents($file . '.progress', serialize($o));
      } else if ($act == 'OPEN' && $o[$h0][$h1] != '3') {
        // IF POST IS OPEN
        $op = unserialize(file_get_contents($file));
        $m = file_get_contents($file . '.m');
        $mm = explode('.', $m)[0];
        $mn = explode('.', $m)[1];
        $mnn = explode('.', $m)[2];
        if ($mm != 0) {
          if ($op[$h0][$h1] == 1) {
            file_put_contents($file . '.m', ($mm - 1) . '.' . $mn . '.' . $mnn);
          } else {
            file_put_contents($file . '.m', $mm . '.' . $mn . '.' . ($mnn + 1));
          }
          $o[$h0][$h1] = 3;
          file_put_contents($file . '.progress', serialize($o));
        } // end.if m!=0
      } else if ($act = 'UNCHECK' && $o[$h0][$h1] != '3') {
        // IF POST IS UNCHECK
        $op = unserialize(file_get_contents($file));
        $o[$h0][$h1] = $op[$h0][$h1];
        file_put_contents($file . '.progress', serialize($o));
      }
    } // end.if not right number
  }

  //-------------------------//
  //---------LOGGED----------//
  //--------SHOW DATA--------//
  //-------------------------//

  // GET DATA
  $t = unserialize(file_get_contents($file));
  $p = unserialize(file_get_contents($file . '.progress'));
  $m = file_get_contents($file . '.m');
  $mm = explode('.', $m)[0];
  $mn = explode('.', $m)[1];
  $dd = explode('.', $m)[2];
  echo '<div>' . ($lv == 0 ? 'Easy' : ($lv == 1 ? 'Normal' : ($lv == 2 ? 'Hard' : 'Very Hard'))) . ' / ' . $nx . 'x' . $ny . '</div>';

  //------SHOW ARRAY-------//
  if ($mm > 0 && $dd != $mn) {
    //-------------------------//
    //------IF TURN > 0--------//
    //-------------------------//
    $msg = $mm < 2 ? '<red>' . $mm . '</red>' : $mm;
    echo <<<EOL
      <div class="green">You have $msg turn left</div>
      
      <form class="form-matrix" action="{$root}index.php" method="post">
        <div class="table-matrix">
          <table width="90%" align="center" cellspacing="0">
            <input type="hidden" name="_x" value="1">
    EOL;
    for ($j = 0; $j < $ny; $j++) {
      for ($i = 0; $i < $nx; $i++) {
        // create b array to display number/radio/boom
        if ($t[$i][$j] != '1') {
          // IMPORTANT: COUNT VALUES AROUND
          @$b[$i][$j] = $t[$i][$j - 1] + $t[$i][$j + 1] + $t[$i - 1][$j] + $t[$i + 1][$j] + $t[$i + 1][$j - 1] + $t[$i + 1][$j + 1] + $t[$i - 1][$j + 1] + $t[$i - 1][$j - 1];
        } else {
          $b[$i][$j] = 'X';
        }
        echo ($i == 0 ? '<tr>' : '') . '<td' . ($p[$i][$j] == 2 || ($b[$i][$j] === 'X' && $p[$i][$j] == 3) ? ' class="check boom"' : '') . '>';
        if ($p[$i][$j] != '3')
          echo '<input type="radio" value="' . $i . '.' . $j . '" name="_id">';
        else
          echo $b[$i][$j];
        echo '</td>' . ($i == $nx - 1 ? '</tr>' : '');
      }
    }
    echo <<<EOL
          </table>
        </div>

        <input type="submit" name="act" value="OPEN"> 
        <input type="submit" name="act" value="CHECK"> 
        <input type="submit" name="act" value="UNCHECK">
      </form>
    EOL;
  } else {
    if ($dd == $mn) {
      //-------------------------//
      //-----------WIN-----------//
      //-------------------------//
      echo <<<EOL
        <div class="green" id="s">Congratulations! You are a boss of this game!</div>
        <div class="table-matrix">
          <table width="90%" align="center" cellspacing="0">
      EOL;
    } else {
      //-------------------------//
      //--------GAME OVER--------//
      //-------------------------//
      $msg = $mn - $dd == 1 ? '<div class="green">You had a good game!</div>' : '';
      echo <<<EOL
        <red>Sorry! The game was over!</red>
        $msg
        <div class="table-matrix">
          <table width="90%" align="center" cellspacing="0">
      EOL;
    }
    // show table
    for ($j = 0; $j < $ny; $j++) {
      for ($i = 0; $i < $nx; $i++) {
        // create b array
        if ($t[$i][$j] != '1') {
          // IMPORTANT: COUNT VALUES AROUND
          @$b[$i][$j] = $t[$i][$j - 1] + $t[$i][$j + 1] + $t[$i - 1][$j] + $t[$i + 1][$j] + $t[$i + 1][$j - 1] + $t[$i + 1][$j + 1] + $t[$i - 1][$j + 1] + $t[$i - 1][$j - 1];
        } else {
          $b[$i][$j] = 'X';
        }
        echo ($i == 0 ? '<tr>' : '') . '<td' . ($b[$i][$j] === 'X' ? ' class="check' . (($p[$i][$j] == 3) ? ' boom' : '') . '"' : '') . '>';
        echo $b[$i][$j];
        echo '</td>' . ($i == $nx - 1 ? '</tr>' : '');
      }
    }
    echo <<<EOL
        </table>
      </div>
    EOL;
  } // END M=0

  // show submit
  echo <<<EOL
    <form action="{$root}index.php" method="post">
      <input type="submit" name="id" value="LOGOUT"> 
      <input type="submit" name="id" value="RESET"> 
      <input type="submit" name="id" value="RELOAD">
    </form>
  EOL;
}

//-------------------------//
//---------FOOTER----------//
//-------------------------//
echo <<<EOL
      iBoom v1.0 ThinhHV.com
      <br/>
      Your IP: $ip
    </center>
  </body>
  <!--ThinhHV author-->
</html>
EOL;
ob_end_flush();
