<?php
require_once (ROOT_PATH .'core'.DS.'3p'.DS.'jpgraph'.DS.'src'.DS.'jpgraph.php');
require_once (ROOT_PATH .'core'.DS.'3p'.DS.'jpgraph'.DS.'src'.DS.'jpgraph_bar.php');
require_once (ROOT_PATH .'core'.DS.'3p'.DS.'jpgraph'.DS.'src'.DS.'jpgraph_line.php');
require_once (ROOT_PATH .'core'.DS.'3p'.DS.'jpgraph'.DS.'src'.DS.'jpgraph_plotline.php');

// prepare workers graph
$dataY = array();
$labelsX = array();
foreach($steps as $stepId => &$step)
{
	$dataY[] = $step['workers'];
	$labelsX[] = ($stepId + 1);
}

// setup the graph
$graph = new Graph(700,300);
$graph->SetScale("textint");

$theme_class = new UniversalTheme;

$graph->SetTheme($theme_class);
$graph->img->SetAntiAliasing(true);
$graph->SetBox(false);
$graph->SetMargin(50,5,50,45);

$graph->title->SetFont(FF_FONT2,FS_BOLD,12);
$graph->title->Set("Workers per Step");
$graph->subtitle->SetFont(FF_FONT1,FS_NORMAL,8);
$graph->subtitle->Set("Batch: " . $id);

$graph->yaxis->HideLine(false);
$graph->yaxis->HideTicks(false, false);
$graph->yaxis->SetTitle("Workers", 'center');
$graph->yaxis->SetTitleMargin(40);

$graph->xgrid->Show();
$graph->xgrid->SetLineStyle("solid");
$graph->xaxis->SetTickLabels($labelsX);
$graph->xaxis->SetTitle("Step", 'center');
$graph->xgrid->SetColor('#E3E3E3');

// plot bars
$p1 = new BarPlot($dataY);
$graph->Add($p1);
$p1->SetColor("olivedrab3");
$p1->SetFillGradient('olivedrab1','olivedrab4',GRAD_VERT);

// output graph to temp file
$graph->Stroke(TMP_PATH.'img-cache'.DS.'workers-'.$id.'.png');


/*
 * render step graphs
 */
foreach($steps as $stepId => &$step)
{
	if ($step['results-cnt'] == 0) continue;
	// prepare graph
	$dataY = array();

	foreach($step['results'] as $wid => &$result)
	{
		$dataY[] = $result[0];
	}

	// setup the graph
	$graph = new Graph(410,180);
	$graph->SetScale("textint");

	$theme_class = new UniversalTheme;

	$graph->SetTheme($theme_class);
	$graph->img->SetAntiAliasing(true);
	$graph->SetBox(false);
	$graph->SetMargin(50,5,15,15);

	$graph->xaxis->HideLabels();

	$graph->yaxis->HideLine(false);
	$graph->yaxis->HideTicks(false, false);

	// plot bars
	$p1 = new BarPlot($dataY);
	$graph->Add($p1);
	$p1->SetColor("olivedrab3");
	$p1->SetFillGradient('olivedrab1','olivedrab4',GRAD_VERT);

	$pAvg = new PlotLine(HORIZONTAL, $step['results-avg'], '#000000', 2);
	$graph->Add($pAvg);

	$pMin = new PlotLine(HORIZONTAL, $step['results-min'], '#008800', 2);
	$graph->Add($pMin);

	$pMax = new PlotLine(HORIZONTAL, $step['results-max'], '#ff0000', 2);
	$graph->Add($pMax);

	$pSd1 = new PlotLine(HORIZONTAL, $step['results-avg'] + $step['results-sd'] / 2, '#0000ff', 2);
	$graph->Add($pSd1);

	$pSd2 = new PlotLine(HORIZONTAL, $step['results-avg'] - $step['results-sd'] / 2, '#0000ff', 2);
	$graph->Add($pSd2);

	// output graph to temp file
	$graph->Stroke(TMP_PATH.'img-cache'.DS.'results-'.$id.'-' . $stepId .'.png');
}


?>

<h3>Workers</h3>

<img src="<?= BASE_URL.'core/tmp/img-cache/workers-'.$id.'.png' ?>">

<h3>Consolidated Results</h3>
<table class="steps">

<?php foreach($steps as $stepId => &$step): 
	$rows = 2;
	if ($stepId > 0) $rows += 3;
	if ($step['results-cnt'] > 0) $rows += 4;
?>
	<tr class="step">
		<td class="number" rowspan="<?= $rows ?>"><?= ($stepId + 1) ?></td>
		<td class="command" colspan="3"><?= $step['command'] ?></td>
		<td></td>
	</tr>
	<tr class="property">
		<td class="property-key" colspan="2">workers</td>
		<td class="property-value"><?= $step['workers'] ?></td>
		<td rowspan="<?= ($rows - 1)?>">
			<?php if ($step['results-cnt'] > 0): ?>
			<img src="<?= BASE_URL.'core/tmp/img-cache/results-'.$id.'-'.$stepId.'.png' ?>">
			<?php endif; ?>
		</td>
	</tr>
	<?php if ($stepId > 0): ?>
	<tr class="property">
		<td class="property-key" rowspan="3">duration</td>
		<td class="property-key2">average</td>
		<td class="property-value"><?= date('i:s', $step['duration-avg']) ?></td>
	</tr>
	<tr class="property">
		<td class="property-key2">maximum</td>
		<td class="property-value"><?= date('i:s', $step['duration-max']) ?></td>
	</tr>
	<tr class="property">
		<td class="property-key2">minimum</td>
		<td class="property-value"><?= date('i:s', $step['duration-min']) ?></td>
	</tr>
	<?php endif; ?>
	<?php if ($step['results-cnt'] > 0): ?>
	<tr class="property">
		<td class="property-key" rowspan="4">result</td>
		<td class="property-key2">average</td>
		<td class="property-value"><?= round($step['results-avg'], 1) ?></td>
	</tr>
	<tr class="property">
		<td class="property-key2" style="color:blue;">standard deviation</td>
		<td class="property-value"><?= round($step['results-sd'], 1) ?></td>
	</tr>
	<tr class="property">
		<td class="property-key2" style="color:red;">maximum</td>
		<td class="property-value"><?= $step['results-max'] ?></td>
	</tr>
	<tr class="property">
		<td class="property-key2" style="color:#008800;">minimum</td>
		<td class="property-value"><?= $step['results-min'] ?></td>
	</tr>
	<?php endif; ?>
<?php endforeach; ?>
</table>

