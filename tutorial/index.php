<?php
function entry($img, $text) {
	echo '<tr>' . 
		'<td class="image"><img src="images/' . $img . '" class="screenshot"/></td>' . 
		'<td class="description">' . $text . '</td>' . 
		'</tr>';
}

function tabButton($caption, $name) {
	echo '<input '.
			   'id="button_' . $name . '"' .
			   'type="button" ' .
			   'name="' . $name . '" ' .
			   'class="tabbutton"' .
			   'value="' . $caption . '"' .
			   'title=""' .
			   'onMouseOver="goLite(this.form.name,this.name)"' .
			   'onMouseOut="goDim(this.form.name,this.name)"' .
			   'onClick="activeTab(\'' . $name . '\')">';
}
?>

<html dir="ltr" lang="lt-LT"> 
<head> 
	<meta charset="UTF-8" /> 
	<title>CULS - Naujienų tvarkymo aprašymas</title>
	<link rel="stylesheet" type="text/css" href="style.css" />
	<script type="text/javascript">
		function activeTab(tab) {
			window.document.getElementById('pridejimas').style.display = "none";
			window.document.getElementById('redagavimas').style.display = "none";
			window.document.getElementById('paveiksliukai').style.display = "none";
			window.document.getElementById(tab).style.display = "block";
			window.document.getElementById('button_pridejimas').style.color = "#BBBBBB";
			window.document.getElementById('button_redagavimas').style.color = "#BBBBBB";
			window.document.getElementById('button_paveiksliukai').style.color = "#BBBBBB";
			window.document.getElementById('button_' + tab).style.color = "#FFFFFF";
		}
		
		function goLite(FRM,BTN) {
		   window.document.forms[FRM].elements[BTN].style.backgroundColor = "#444444";
		}

		function goDim(FRM,BTN) {
		   window.document.forms[FRM].elements[BTN].style.backgroundColor = "#000000";
		}
	</script>
</head>
<body class="tutorial">
	<div class="main">
		<div class="header">
			<h1>CULS - Naujienų tvarkymo aprašymas</h1>
			<form name="tabform" class="tabs">
				<table class="tabs">
					<tr><td>
						<?php
							tabButton('Naujienos pridėjimas', 'pridejimas');
							tabButton('Naujienų redagavimas', 'redagavimas');
							tabButton('Paveiksliukų įkėlimas', 'paveiksliukai');
						?>
						</td>
					</tr>
				</table>
			</form>
		</div> <!-- header -->
		
		<div id="pridejimas" style="display: none">
			<h2><a name="pridejimas"/>Naujienos pridėjimas</h2>
			<table class="steplist">
			<?php
				entry('step1.gif', 'Norėdami pridėti naujieną, meniu juostoje pasirinkite <b>Add New</b>.');
				entry('step2.gif', 'Įveskite naujienos pavadinimą.');
				entry('step3.gif', 'Įveskite naujienos tekstą.');
				entry('step4.gif', '<b>Discussion</b> skiltyje nužymėkite <b>Allow trackbacks ...</b> ' .
								   'ir pažymėkite <b>Allow comments</b>, jei norite, kad prie naujienos būtų galima ' .
								   'palikti komentarų.');
				entry('step5.gif', '<b>Language</b> skiltyje pasirinkite <b>Lithuanian</b>.');
				entry('step6.gif', '<b>Categories</b> skiltyje pažymėkite <b>Naujienos</b>.');
				entry('step7.gif', '<b>Publish</b> skilties mygtukai:<ul>' . 
								   '<li><b>Save Draft</b> - išsaugo naujieną, bet dar nepublikuoja;</li>' .
								   '<li><b>Preview</b> - parodo kaip naujiena atrodytų CULS puslapyje;</li>' .
								   '<li><b>Publish</b> - įdeda naujieną į CULS puslapį.</li>' .
								   '</ul>');
				entry('step9.gif', 'Įvedę lietuvišką naujieną <b>Lanuage</b> skiltyje prie <b>English</b> paspauskite <b>add</b>. '.
								   'Atsivers langas, kur galėsite įvesti anglišką naujienos vertimą. Visi žingsniai tokie patys ' .
								   'kaip ir lietuviškame variante.');
				entry('step10.gif','Jei ką nors pakeitėte jau publikuotoje naujienoje <b>Publish</b> skiltyje spauskite <b>Update</b>.');
			?>
			</table>
		</div> <!-- pridejimas -->
		
		<div id="redagavimas" style="display: none">
			<h2><a name="redagavimas"/>Naujienų redagavimas</h2>
			<table class="steplist">
			<?php
				entry('step11.gif', 'Norėdami redaguoti arba peržiūrėti naujienas, meniu juostoje pasirinkite <b>Posts</b>.');
				entry('step12.gif', 'Naujienų sąraše, prie pasirinktos naujienos, spauskite <b>Edit</b>.');
			?>
			</table>
		</div> <!-- redagavimas -->
		
		<div id="paveiksliukai" style="display: none">
			<h2><a name="paveiksliukai"/>Paveiksliukų įkėlimas</h2>
			<table class="steplist">
			<?php
				entry('step13.gif', 'Norėdami į naujieną įterpti paveiksliuką, virš naujienos teksto paspauskite '.
									'paveiksliuko ikoną.');
				entry('step14.gif', 'Spauskite <b>Select Files</b> ir pasirinkite paveiksliuką, kurį norite įkelti į serverį.');
				entry('step15.gif', 'Įveskite URL kelią, kuriuo nueis paspaudus paveiksliuką:<ul>' .
									'<li><b>None</b> - paveiksliuko paspausti negalima;</li>' .
									'<li><b>File</b> - atidarys paveiksliuką;</li>' .
									'<li><b>Post</b> - atidarys naujieną.</li>' .
									'</ul>' .
									'Taip pat pasirinkite, kurioje teksto vietoje ir kokio dydžio paveiksliukas bus įterptas.');
				entry('step16.gif', 'Norėdami įterpti į serverį jau įkeltą paveiksliuką, paspauskite paveiksliuko ikoną ' .
									'ir pasirinkite <b>Media Library</b> skiltį.');
			?>
			</table>
		</div> <!-- paveiksliukai -->
		
		<div class="footer">
			&copy; 2010 Cambridge University Lithuanian Society
		</div> <!-- footer -->
	</div> <!-- main -->
	<script type="text/javascript"> activeTab('pridejimas'); </script>
</body>
</html>