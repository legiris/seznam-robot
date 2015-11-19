<?php
    require_once 'SeznamRobot.php';
    
    if (isset($_POST['submit']) && $_POST['submit'] == 'Vyhledat pozici') {
        $userUrl = $_POST['url'];
        $input = $_POST['input'];
        
        $seznamRobot = new SeznamRobot($userUrl, $input);
        $seznamRobot->setPageLimit(20);
        $seznamRobot->loader();
        
        $urlPosition = $seznamRobot->getUrlPosition();
        $urlOutput = $seznamRobot->getUrlOutput();
        
        header('Location: index.php?position=' . $urlPosition . '&output=' . $urlOutput);
    }
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <meta name="description" content="" />
    <title>PHP test</title>
    <link rel="shortcut icon" href="/favicon.ico" />
  </head>	
  <body>
    <div style="width: 800px; margin: 0 auto; border: 1px solid black; padding: 10px; min-height: 875px;">
        <p style="text-align: right;"><a href="index.php">Homepage</a></p>  
      <h2>PHP testing code</h2>	
      
      <form method="POST">
        <table>
          <tr>
            <td>Zadejte URL:</td>
            <td><input type="text" id="url" name="url" /></td>
          </tr>
          <tr>
            <td>Vyhledávat:</td>
            <td><input type="text" id="input" name="input" /></td>
          </tr>
          <tr>
            <td><input type="submit" name="submit" value="Vyhledat pozici" /></td>
            <td><input type="reset" value="Smazat" /></td>
          </tr>
        </table>
      </form>
      <hr />
      <?php
        if (isset($_GET['position'])) {
            echo '<p>Pozice: ' . ($_GET['position'] == '' ? 'Nenalezeno' : $_GET['position']) . '</p>';
        }
        if (isset($_GET['output'])) {
            if ($_GET['output'] == '') {
                echo '<p>Odkaz: Nenalezeno</p>';
            } else {
                echo '<p>Odkaz: <a href="' . $_GET['output'] . '" target="_blank" title="Přejít">' . $_GET['output'] . '</p>';
            }
        }
      ?>
    </div>
  </body>
</html>