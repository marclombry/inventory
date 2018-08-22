<?php
function debug($v,$mode=1){
		if($mode==1){
			echo '<pre style="font-size:28px;background-color:red;border:2px solid black;font-weight:bold;">';
				var_dump($v);
			echo '</pre>';
			exit();
		}else{
			echo '<pre style="font-size:28px;background-color:yellow;border:2px solid black;font-weight:bold;">';
				print_r($v);
			echo '</pre>';
			exit();
		}
	}
	
	// connexion base de donnée //
$dsn = 'mysql:host=localhost;dbname=inventaires';
$user = 'root';
$password = '';

try {
    $bdd = new PDO($dsn, $user, $password);
} catch (PDOException $e) {
    echo 'Connexion échouée : ' . $e->getMessage();
}
$afficheImport='';
$reponseImport='';
	if($_POST){
		
		// on recupere le chemins du dossier ou upload les fichiers
		$upload_dir=dirname(__FILE__).'/fichiers/';
		// recuperer l'emplacement du fichier temporaire et le nom du fichier
			$temp_file=$_FILES['fiche']['tmp_name'];
			$dest_file=date('d-m-Y_H-i-s').'.txt';
		//si le déplacement est réussi on traite le fichier //	
			if (move_uploaded_file($temp_file, $upload_dir.$dest_file)) {
			    $reponseImport = "<div class='ras'>L'envoi a bien été effectué !</div>";
			    
				$myfile = fopen($upload_dir.$dest_file, "r") or die("erreur sur le fichiers!");
				//tant que (feof repressente la fin du fichier) n'est pas atteint, je traite chaque ligne
					
				while(!feof($myfile)){
					$str = fgets($myfile);
					//var_dump($str);
					$point =".";
					$position = strpos($str,$point);
					$reference = substr($str,0,$position);

					//Initialisation du compteur d'inventaire
					$select = "SELECT max(idComptage) as idComptage FROM inventaire WHERE reference='$reference'";	
					$q = $bdd->query($select);
					
					$idCpt = $q->fetch();
					
					$cpt = (isset($idCpt['idComptage']) ? $idCpt['idComptage']+1 : 1);

					$quantite = substr($str,$position+1);// retourne la position apres le point(pour avoir le nombre)
						//echo "<h1>$reference ================ $quantite</h1>";
						//creation de la variable qui stock l'affichage de l'importation//
						$afficheImport.="<tr>
											<td>$reference</td>
											<td>$quantite</td>
											<td>$cpt</td>
										 </tr>";
										
					
						// ICI ON INSERERA EN BASE DE DONNER//
						
						$sql = "INSERT INTO inventaire (id,idComptage,reference,quantite,dates,export)VALUES(1,:cpt,:reference,:quantite,now(),false)";
						$insert = $bdd->prepare($sql);
						$insert->bindParam(':cpt',$cpt);
						$insert->bindParam(':reference',$reference);
						$insert->bindParam(':quantite',$quantite);
						$insert->execute();
						
					

				}
				
				fclose($myfile);
				
			} else {
			    $reponseImport="<div class='btn-warning'>Echec veuillez recommencer</div>";
			}
	
		
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<title>inventaire de secours</title>
		<meta charset='utf-8'>
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		
		<style>
			.center{margin:0 auto;margin-top:60px;}
			.tableImport tr td,.tableImport th{
				padding:20px;
				background-color:#A3F5A3;
				text-align:center;
				
			}
			.padL40{font-size:22px;background-color:#CAEEFF;}
			.pad{padding:0 40px;}
			#documentation{
				background-color:#FFCAB1;
			}
			#importManuelle{
				
			}
		</style>
		<script type="text/javascript" src="../JS/jquery.js"></script>
		<script type="text/javascript" src="../JS/jquery-ui-1.10.4.custom.min.js"></script>
		<!-- <link href="http://fonts.googleapis.com/css?family=Droid+Sans+Mono" rel="stylesheet" type="text/css"> -->
		<link rel="stylesheet" href="../CSS/bootstrap/css/bootstrap.css">
		<link rel="stylesheet" href="../CSS/bootstrapComplement.css">
		<link rel="stylesheet" href="../JS/css/ui-lightness/jquery-ui-1.10.4.custom.css">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
	</head>
	<body>
	<div id="div_header">
			<h1 class="text-center">Importation manuelle</h1>
			<hr />
	</div>
	<div class="row padL40">
		<div id="documentation" class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
			<h2 class="text-center">Guide d'utilisation</h2>
			<p class="pad" style="font-size:18px;">
				1. Dans un fichier texte (.txt), scannez une référence puis saisissez la quantité précédée d'un point.<br />
				2. Retournez à la ligne et répétez l'opération précédente (format à respecter : référence.quantité).<br />
				3. Une fois terminé, enregistrez le fichier (format .txt) puis utilisez cet outil pour importer le fichier.<br />
				4. Cliquez sur "Valider" pour mettre à jour l'inventaire et voir apparaitre les références et quantités ajoutées.
			</p>
		
		</div>
		<div id="importManuelle" class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
			<h2 class="text-center">Importation Manuelle</h2>
			<form enctype="multipart/form-data" method='post' action=''>
				<input type="hidden" name="MAX_FILE_SIZE" value="30000" />
					<p>Fichier à importer: <input type="file" name="fiche"></p>
					<p><input type ="submit" name="valider" value="valider"></p>
			</form>
			<?php echo $reponseImport;  ?>
			
		</div>
	</div>
	<div class="row">
		<table class="table-bordered tableImport center padL40">
					<tr>
						<th>Référence</th>
						<th>Quantité</th>
						<th>Comptage n°</th>
					</tr>
					
						<?php echo $afficheImport;?>
						
					
		</table>
	</div>
	</body>
</html>