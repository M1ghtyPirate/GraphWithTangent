<?php
	// Ширина и высота изображения, минимальные отступы от краев, концов координат
	$W=1280;
	$H=1024;
	$M1=min($W, $H)*0.03;
	$M2=$M1;
	
	// Параметры кривой и касательной
	$R=100;
	$l=50;
	$Rot=2*M_PI;
	$Steps=200;
	$TangentDeg=270;

	//header ("Content-type: image/png");
	$im = imagecreatetruecolor($W, $H);
	
	//Цвета элементов
	$bg = imagecolorallocate($im, 255, 255, 255);
	$pcolor = imagecolorallocate($im, 255, 0, 0);
	$lcolor = imagecolorallocate($im, 0, 0, 0);
	$axiscolor = imagecolorallocate($im, 100, 100, 100);
	$textcolor = imagecolorallocate($im, 50, 50, 50);
	$tangentcolor = imagecolorallocate($im, 255, 0, 0);
	
	//Размер, форма точек
	$psize=min($W, $H)*0.01;
	//$pshape="circle";
	//$pshape="rectangle";
	//$pshape="triangle";
	$pshape="cross";
	//$pshape="plus";
	
	//Толщина линий
	$lthickness=min($W, $H)*0.0025;
	$tangentthickness=min($W, $H)*0.0025;
	
	//Толщина осей координат, размер подписей
	$axisthickness=min($W, $H)*0.02*0.1;
	$axismod=min($W, $H)*0.02;
	$doxline=10;
	$doyline=10;
	$oxlfreq=2;
	$oylfreq=$oxlfreq;
	$sfont=4;
	$LW=imagefontwidth($sfont);
	$LH=imagefontHeight($sfont);
	
	//Отрисовка точки
	function point ($im, $x, $y, $psize, $pcolor, $pshape="circle") {
		switch($pshape) {
			case "circle":
				imagefilledellipse($im, $x, $y, $psize, $psize, $pcolor);
				break;
			case "rectangle":
				imagefilledrectangle($im, $x-$psize/2, $y-$psize/2, $x+$psize/2, $y+$psize/2, $pcolor);
				break;
			case "triangle":
				$tpoints=array($x-$psize/2, $y+($psize/2)*tan(M_PI/6), $x, $y-($psize/2)/cos(M_PI/6), $x+$psize/2, $y+($psize/2)*tan(M_PI/6));
				imagefilledpolygon($im, $tpoints, 3, $pcolor);
				break;
			case "cross":
				imagelinethick($im, $x-$psize/2, $y-$psize/2, $x+$psize/2, $y+$psize/2, $pcolor , $psize/4);
				imagelinethick($im, $x-$psize/2, $y+$psize/2, $x+$psize/2, $y-$psize/2, $pcolor , $psize/4);
				break;
			case "plus":
				imagelinethick($im, $x, $y-$psize/2, $x, $y+$psize/2, $pcolor , $psize/4);
				imagelinethick($im, $x-$psize/2, $y, $x+$psize/2, $y, $pcolor , $psize/4);
				break;
			default:
				imagefilledellipse($im, $x, $y, $psize, $psize, $pcolor);
		}
	}

	//Отрисовка толстой линии
	function imagelinethick($image, $x1, $y1, $x2, $y2, $color , $thick = 1) {
		if ($thick == 1) {
			return imageline($image, $x1, $y1, $x2, $y2 , $color);
		}
		$t = $thick / 2;
		
		if ($x1 == $x2 || $y1 == $y2) {
			$tvx=($x1==$x2)?1:0;
			$tvy=($y1==$y2)?1:0;
			return imagefilledrectangle($image,
				   min($x1 , $x2) - $tvx*$t,
				   min($y1 , $y2) - $tvy*$t,
				   max($x1 , $x2) + $tvx*$t,
				   max($y1 , $y2) + $tvy*$t, $color);
		}
		
		$length=sqrt(pow(($y2-$y1), 2) + pow(($x2-$x1), 2));
		$w=abs($x1-$x2);
		$h=abs($y1-$y2);
		$cosa=$h/$length;
		$sina=$w/$length;
		$tw=$t*$cosa;
		$th=$t*$sina;
		$ys=($y2-$y1)/abs($y2-$y1);
		$xs=($x2-$x1)/abs($x2-$x1);
		$points = array(
			$x1+$ys*$tw, $y1-$xs*$th,
			$x2+$ys*$tw, $y2-$xs*$th,
			$x2-$ys*$tw, $y2+$xs*$th,
			$x1-$ys*$tw, $y1+$xs*$th
			);

		
		imagefilledpolygon($image, $points, 4, $color) ;
		return imagepolygon($image , $points, 4, $color );
	}
	
	/*
	//Без сохранения пропорций
	//Преобразование координаты X
	function X($x) {
			global $minx, $maxx, $W, $M1, $M2;
			return (int) ($M1+$M2+($x-$minx)*($W-2*($M1+$M2))/($maxx-$minx));
	}
	
	//Преобразование координаты Y
	function Y($y) {
			global $miny, $maxy, $H, $M1, $M2;
			return (int) ($M1+$M2+($maxy-$y)*($H-2*($M1+$M2))/($maxy-$miny));
	}
	*/
	
	//С сохранением пропорций
	//Преобразование координаты X
	function X($x) {
			global $minx, $maxw, $maxneww, $W;
			return (int) (($W-$maxneww)/2+($x-$minx)*($W-($W-$maxneww))/$maxw);
	}
	
	//Преобразование координаты Y
	function Y($y) {
			global $maxy, $maxh, $maxnewh, $H;
			return (int) (($H-$maxnewh)/2+($maxy-$y)*($H-($H-$maxnewh))/$maxh);
	}
	
	//Заливка фона
	imagefill($im, 0, 0, $bg);
	
	//Кривая расчет точек
	$i=0;
	$dj=$Rot/$Steps;
	for ($j=0;$j<=$Rot;$j+=$dj) {
		$r=2*$R*cos($j)+$l;
		$x[$i]=$r*cos($j);
		$y[$i]=$r*sin($j);
		$i++;
	}
	
	$minx=min($x);
	$miny=min($y);
	$maxx=max($x);
	$maxy=max($y);
	$maxw=abs($maxx-$minx);
	$maxh=abs($maxy-$miny);
	
	//Пропорции сохраняются
	if ($maxw/($W-2*($M1+$M2))>$maxh/($H-2*($M1+$M2))) {
		$maxneww=$W-2*($M1+$M2);
		$maxnewh=$maxneww*$maxh/$maxw;
	}
	else {
		$maxnewh=$H-2*($M1+$M2);
		$maxneww=$maxnewh*$maxw/$maxh;
	}
	
	//Оси координат
	imagelinethick($im, X(0), $M1, X(0), $H-$M1, $axiscolor, $axisthickness);
	imagelinethick($im, $M1, Y(0), $W-$M1, Y(0), $axiscolor, $axisthickness);
	imagelinethick($im, X(0), $M1, X(0)-0.25*$axismod, $M1+$axismod, $axiscolor, $axisthickness);	
	imagelinethick($im, X(0), $M1, X(0)+0.25*$axismod, $M1+$axismod, $axiscolor, $axisthickness);
	imagelinethick($im, $W-$M1, Y(0), $W-$M1-$axismod, Y(0)-0.25*$axismod, $axiscolor, $axisthickness);	
	imagelinethick($im, $W-$M1, Y(0), $W-$M1-$axismod, Y(0)+0.25*$axismod, $axiscolor, $axisthickness);
	$oxline=$doxline;
	while (X($oxline)<($W-$M1)) {
		imagelinethick($im, X($oxline), Y(0)-$axismod/4, X($oxline), Y(0)+$axismod/4, $axiscolor, $axisthickness/2);
		if ($oxline%($oxlfreq*$doxline)==0) {
			imagestring($im, $sfont, X($oxline)-(strlen($oxline)*$LW)/2, Y(0)+$LH/2+$axismod/4, $oxline, $textcolor);
		}
		$oxline+=$doxline;
	}
	$oxline=-$doxline;
	while (X($oxline)>$M1) {
		imagelinethick($im, X($oxline), Y(0)-$axismod/4, X($oxline), Y(0)+$axismod/4, $axiscolor, $axisthickness/2);
		if ($oxline%($oxlfreq*$doxline)==0) {
			imagestring($im, $sfont, X($oxline)-(strlen($oxline)*$LW)/2, Y(0)+$LH/2+$axismod/4, $oxline, $textcolor);
		}
		$oxline=$oxline-$doxline;
	}
	$oyline=$doyline;
	while (Y($oyline)>$M1) {
		imagelinethick($im, X(0)+$axismod/4, Y($oyline), X(0)-$axismod/4, Y($oyline), $axiscolor, $axisthickness/2);
		if ($oyline%($oylfreq*$doyline)==0) {
			imagestring($im, $sfont, X(0)-$axismod-(strlen($oyline)*$LW)/2-$axismod/4, Y($oyline)-$LH/2, $oyline, $textcolor);
		}
		$oyline+=$doyline;
	}
	$oyline=-$doyline;
	while (Y($oyline)<($H-$M1)) {
		imagelinethick($im, X(0)+$axismod/4, Y($oyline), X(0)-$axismod/4, Y($oyline), $axiscolor, $axisthickness/2);
		if ($oyline%($oylfreq*$doyline)==0) {
			imagestring($im, $sfont, X(0)-$axismod-(strlen($oyline)*$LW)/2-$axismod/4, Y($oyline)-$LH/2, $oyline, $textcolor);
		}
		
		$oyline=$oyline-$doyline;
	}
	imagestring($im, $sfont, X(0)-$axismod-(strlen("0")*$LW)/2-$axismod/4, Y(0)+$LH/2+$axismod/4, "0", $textcolor);
	imagestring($im, $sfont, $W-$M1+$axismod-(strlen("x")*$LW)/2, Y(0)+$LH/2+$axismod/4, "x", $textcolor);
	imagestring($im, $sfont, X(0)-$axismod-(strlen("y")*$LW)/2-$axismod/4, $M1-$axismod-$LH/2, "y", $textcolor);
	
	//Кривая
	for ($i=1;$i<count($x);$i++) {
		imagelinethick($im, X($x[$i-1]), Y($y[$i-1]), X($x[$i]), Y($y[$i]), $lcolor, $lthickness);
	}
	
	//Касательная
	$TangentRad=2*M_PI*($TangentDeg/360);
	
	$r=2*$R*cos($TangentRad)+$l;
	$x1=$r*cos($TangentRad);
	$y1=$r*sin($TangentRad);
	if ($TangentDeg%180!=0) {
		$dyTD=(-2*$R*sin($TangentRad)*sin($TangentRad)+(2*$R*cos($TangentRad)+$l)*cos($TangentRad))/(-2*$R*sin($TangentRad)*cos($TangentRad)-(2*$R*cos($TangentRad)+$l)*sin($TangentRad));
		$x2=$minx-floor((X($minx)-$M1)/(X(1)-X(0)));
		$x3=$maxx+floor(($W-$M1-X($maxx))/(X(1)-X(0)));
		$y2=$dyTD*($x2-$x1)+$y1;
		$y3=$dyTD*($x3-$x1)+$y1;
		if (Y($y2)<($M1)) {
			$y2=$maxy+floor((Y($maxy)-$M1)/(Y(0)-Y(1)));
			$x2=(($y2-$y1)/$dyTD)+$x1;
		}
		if (Y($y2)>($H-$M1)) {
			$y2=$miny-floor(($H-$M1-Y($miny))/(Y(0)-Y(1)));
			$x2=(($y2-$y1)/$dyTD)+$x1;
		}
		if (Y($y3)<($M1)) {
			$y3=$maxy+floor((Y($maxy)-$M1)/(Y(0)-Y(1)));
			$x3=(($y3-$y1)/$dyTD)+$x1;
		}
		if (Y($y3)>($H-$M1)) {
			$y3=$miny-floor(($H-$M1-Y($miny))/(Y(0)-Y(1)));
			$x3=(($y3-$y1)/$dyTD)+$x1;
		}
	}
	else {
		$x2=$x1;
		$x3=$x1;
		$y2=$maxy+floor((Y($maxy)-$M1)/(Y(0)-Y(1)));
		$y3=$miny-floor(($H-$M1-Y($miny))/(Y(0)-Y(1)));
	}
	imagelinethick($im, X($x2), Y($y2), X($x3), Y($y3), $tangentcolor, $tangentthickness);
	
	/*
	//Точки
	for ($i=0;$i<count($x);$i++) {
		point ($im, X($x[$i]), Y($y[$i]), $psize, $pcolor, $pshape);
	}
	*/

	//imagepng($im);
	imagepng($im, "graph_03.png");
	imagedestroy($im);
?>