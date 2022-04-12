<?php
	// Ширина и высота изображения, отступы от краев
	$W=1280;
	$H=1024;
	$M=min($W, $H)*0.03;
	
	
	$x=array(0, 1, 8, 27, 64, 125, 216);
	$y=array(0, 1, 2, 3, 4, 5, 6);
	
	$minx=min($x);
	$miny=min($y);
	$maxx=max($x);
	$maxy=max($y);
	
	$minb = $miny-2;
	$mina = $minx-20;
	$maxb = $maxy+2;
	$maxa = $maxx+100;

	//header ("Content-type: image/png");
	$im = imagecreatetruecolor($W, $H);
	
	//Цвета элементов
	$bg = imagecolorallocate($im, 255, 255, 255);
	$pcolor = imagecolorallocate($im, 255, 0, 0);
	$lcolor = imagecolorallocate($im, 0, 0, 0);
	$axiscolor = imagecolorallocate($im, 100, 100, 100);
	$textcolor = imagecolorallocate($im, 50, 50, 50);
	
	//Размер, форма точек
	$psize=min($W, $H)*0.01;
	//$pshape="circle";
	//$pshape="rectangle";
	//$pshape="triangle";
	$pshape="cross";
	//$pshape="plus";
	
	//Толщина линии
	$lthickness=min($W, $H)*0.0025;
	
	//Толщина осей координат, размер подписей
	$axisthickness=min($W, $H)*0.02*0.1;
	$axismod=min($W, $H)*0.02;
	$doxline=10;
	$doyline=1;
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
	
	//Преобразование координаты X
	function X($x) {
			global $minx, $maxx, $W, $M;
			return (int) ($M+($x-$minx)*($W-2*$M)/($maxx-$minx));
	}
	
	//Преобразование координаты Y
	function Y($y) {
			global $miny, $maxy, $H, $M;
			return (int) ($M+($maxy-$y)*($H-2*$M)/($maxy-$miny));
	}
	
	//Полиномом Лагранжа
	function LX($yk) {
		global $x, $y;
		$n = count($y);
		$S = 0.0;
		for($i=0; $i < $n; $i++) {
			$P = $x[$i];
			for ($j=0; $j < $n; $j++) {
				if($i != $j) $P *= ($yk - $y[$j])/($y[$i] - $y[$j]);
			}
			$S += $P;
		}
		return $S;
	}
	
	function LY($xk) {
		global $x, $y;
		$n = count($x);
		$S = 0.0;
		for($i=0; $i < $n; $i++) {
			$P = $y[$i];
			for ($j=0; $j < $n; $j++) {
				if($i != $j) $P *= ($xk - $x[$j])/($x[$i] - $x[$j]);
			}
			$S += $P;
		}
		return $S;
	}
	
	//Заливка фона
	imagefill($im, 0, 0, $bg);
	
	//Оси координат
	imagelinethick($im, $M, $M, $M, $H-$M, $axiscolor, $axisthickness);
	imagelinethick($im, $M, $H-$M, $W-$M, $H-$M, $axiscolor, $axisthickness);
	imagelinethick($im, $M, $M, $M-0.25*$axismod, $M+$axismod, $axiscolor, $axisthickness);	
	imagelinethick($im, $M, $M, $M+0.25*$axismod, $M+$axismod, $axiscolor, $axisthickness);
	imagelinethick($im, $W-$M, $H-$M, $W-$M-$axismod, $H-$M-0.25*$axismod, $axiscolor, $axisthickness);	
	imagelinethick($im, $W-$M, $H-$M, $W-$M-$axismod, $H-$M+0.25*$axismod, $axiscolor, $axisthickness);
	$oxline=$doxline;
	while ($oxline<($maxx-$minx)) {
		imagelinethick($im, X($oxline), $H-$M-$axismod/4, X($oxline), $H-$M+$axismod/4, $axiscolor, $axisthickness/2);
		if ($oxline%($oxlfreq*$doxline)==0) {
			imagestring($im, $sfont, X($oxline)-(strlen($oxline)*$LW)/2, $H-$M/2-$LH/2+$axismod/4, $oxline, $textcolor);
		}
		$oxline+=$doxline;
	}
	$oyline=$doyline;
	while ($oyline<($maxy-$miny)) {
		imagelinethick($im, $M+$axismod/4, Y($oyline), $M-$axismod/4, Y($oyline), $axiscolor, $axisthickness/2);
		if ($oyline%($oylfreq*$doyline)==0) {
			imagestring($im, $sfont, $M/2-(strlen($oyline)*$LW)/2-$axismod/4, Y($oyline)-$LH/2, $oyline, $textcolor);
		}
		$oyline+=$doyline;
	}
	imagestring($im, $sfont, $M/2-(strlen("0")*$LW)/2-$axismod/4, $H-$M/2-$LH/2+$axismod/4, "0", $textcolor);
	imagestring($im, $sfont, $W-$M/2-(strlen("x")*$LW)/2, $H-$M/2-$LH/2+$axismod/4, "x", $textcolor);
	imagestring($im, $sfont, $M/2-(strlen("y")*$LW)/2-$axismod/4, $M/2-$LH/2, "y", $textcolor);
	
	/*
	//График
	for ($i=0;$i<count($x);$i++) {
		//point ($im, X($x[$i]), Y($y[$i]), $psize, $pcolor, $pshape);
		if ($i>0) {
			imagelinethick($im, X($x[$i-1]), Y($y[$i-1]), X($x[$i]), Y($y[$i]), $lcolor, $lthickness);
		}
	}
	*/
	
	//Интерполированный график f(y)
	$dy = ($maxy - $miny) / 100;
	$y0 = $miny;
	
	//$dx = ($maxx - $minx) / 100;
	//$x0 = $minx;
	do {
		
		$y1 = $y0 + $dy;
		$X0 = X(LX($y0));
		$Y0 = Y($y0);
		$X1 = X(LX($y1));
		$Y1 = Y($y1);
		/*
		$x1 = $x0 + $dx;
		$X0 = X($x0);
		$Y0 = Y(LY($x0));
		$X1 = X($x1);
		$Y1 = Y(LY($x1));
		*/
		imagelinethick($im, $X0, $Y0, $X1, $Y1, $lcolor, $lthickness);
		//point($im, $X0, $Y0, $psize, $axiscolor, "circle");
		
		$y0 = $y1;
		//$x0 = $x1;
	} while($y1+$dy <= $maxy);
	//} while($x1+$dx <= $maxx);
	
	//Точки
	for ($i=0;$i<count($x);$i++) {
		point ($im, X($x[$i]), Y($y[$i]), $psize, $pcolor, $pshape);
	}

	//imagepng($im);
	imagepng($im, "graph_02.png");
	imagedestroy($im);
?>