<pre>
<?php
	$config = json_decode(file_get_contents("batch-animation.json"));
	var_dump($config);
	if (!file_exists($config->output)) {
		mkdir($config->output, 0777, TRUE);
	}

	$previousFilename = NULL;
	if (isset($_POST['pngData'])) {
		$filename = str_replace("/", "_", $_POST['filename']);
		$origFilename = $config->input.$filename;
		if (file_exists($origFilename)) {
			$filename = explode(".", $filename);
			$filename[count($filename) - 1] = "png";
			$filename = implode(".", $filename);
			$previousFilename = $config->output.$filename;
			if (file_put_contents($config->output.$filename, base64_decode($_POST['pngData']))) {
				echo "PNG saved to: ".$filename."<br>";
				unlink($origFilename);
			} else {
				die("Error writing file: ".$config->output.$filename);
			}
		}
	}
	
	$filename = NULL;
	$files = scandir($config->input);
	shuffle($files);
	foreach ($files as $f) {
		if ($f[0] == ".") {
			continue;
		}
		$filename = $f;
	}
	if ($filename == NULL) {
		die ("All files processed");
	}
?>
</pre>
<style>
	#target {
		border: 1px solid black;
		border-radius: 5px;
		margin-right: 1em;
	}
	
	#status {
	}
</style>
<?php
	if ($previousFilename != NULL) {
		echo '<a name="prev">Previous image:</a><br>';
		echo "<img src=\"".htmlentities($previousFilename)."\"><hr>";
	}
?>
<canvas id="target"></canvas><br>
<div id="status"></div>
<hr>
<form id="img-form" action="?#prev" method="POST">
	<input name="filename" type="hidden" id="filename"></input>
	<textarea name="pngData" id="base64-data" style="width: 100%; height: 5em;"></textarea><br>
</form>

<script src="sketch.js"></script>
<script>
	var filename = <?php echo json_encode($filename);?>;
	var config = <?php echo json_encode($config);?>;

	function setStatus(message) {
		document.title = filename + ": " + message;
		var statusBox = document.getElementById("status");
		statusBox.innerHTML = message;
	}

	var canvas = document.getElementById("target");
	
	document.getElementById("filename").value = filename;
	setStatus("Starting processing");

	var img = new Image();
	img.onload = function() {
		setStatus("Image loaded successfully");
		setTimeout(function () {
			setStatus("Scanning for required textures...");
			var sketcher = processImage(img);
			sketcher.whenReady(function () {
				setStatus("Done - uploading...");
				var dataUrl = canvas.toDataURL();
				var base64 = dataUrl.replace(/^data:image\/\w+;base64,/, "");
				document.getElementById("base64-data").value = base64;
				document.getElementById("img-form").submit();
			});
			sketcher.progressUpdate(function (proportion, message) {
				setStatus("Creating textures: " + (Math.round(proportion*1000)/10) + "% done - " + message);
			});
		}, 1000);
	};
	img.src = "source/" + filename;
	
	function processImage(img) {
		var context = canvas.getContext("2d");
		canvas.width = img.width;
		canvas.height = img.height;

		var sketcher = new Sketcher(canvas.width, canvas.height);
		sketcher.levelSteps = config.levelSteps || 5;
		sketcher.maxTextures = config.maxTextures || 200;
		sketcher.lineAlpha = (config.lineAlpha || 0.2);
		if (config.lineAlphaVariation) {
			sketcher.lineAlpha += (Math.random() - 0.5)*config.lineAlphaVariation;
		}
		sketcher.lineThickness = config.lineThickness || 2;
		sketcher.lineDensity = config.lineDensity || 0.5;
		sketcher.lightness = (config.lightness != undefined) ? config.lightness : 4;
		sketcher.edgeBlurAmount = config.edgeBlurAmount || 2;
		sketcher.edgeAmount = (config.edgeAmount || 0.5);
		if (config.edgeAmountVariation) {
			sketcher.edgeAmount + (Math.random() - 0.5)*config.edgeAmountVariation;
		}
		var greyscale = config.greyscale;

		context.drawImage(img, 0, 0, canvas.width, canvas.height);
		sketcher.transformCanvas(canvas, greyscale);
		return sketcher;
	}
</script>
