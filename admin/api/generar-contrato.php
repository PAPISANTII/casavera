<?php
require '../config.php';
requireAuth();
function fechaEspanol($fecha) {
    $meses = [
        1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
        5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
        9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
    ];
    $dt  = new DateTime($fecha);
    $dia = $dt->format('d');
    $mes = $meses[(int)$dt->format('n')];
    $anio = $dt->format('Y');
    return $dia . ' de ' . $mes . ' de ' . $anio;
}

$id = intval($_GET['id'] ?? 0);
if (!$id) { http_response_code(400); exit('ID no válido'); }

$stmt = $pdo->prepare('
    SELECT c.fecha_inicio, c.fecha_fin, c.precio_mensual, c.fianza,
           i.nombre, i.apellidos, i.nacionalidad, i.documento, i.direccion, i.email, i.telefono,
           h.numero AS hab_numero, h.planta, h.tipo AS hab_tipo
    FROM contratos c
    JOIN inquilinos i ON c.inquilino_id = i.id
    JOIN habitaciones h ON c.habitacion_id = h.id
    WHERE c.id = ?
');
$stmt->execute([$id]);
$d = $stmt->fetch();
if (!$d) { http_response_code(404); exit('Contrato no encontrado'); }

require '../tcpdf/tcpdf.php';

$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8');
$pdf->SetCreator('Casa Vera');
$pdf->SetAuthor('Casa Vera');
$pdf->SetTitle('Contrato de Arrendamiento de Temporada');
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(25, 25, 25);
$pdf->SetAutoPageBreak(true, 20);
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 10);

// Variables dinámicas
$fechaContrato = fechaEspanol($d['fecha_inicio']);
$fechaInicio   = fechaEspanol($d['fecha_inicio']);
$fechaFin      = fechaEspanol($d['fecha_fin']);
$fechaFinCorta = date('d/m/Y', strtotime($d['fecha_fin']));
$nombreCompleto = $d['nombre'] . ' ' . $d['apellidos'];
$precio         = number_format($d['precio_mensual'], 0, ',', '.') . '€';
$habNum         = 'H' . str_pad($d['hab_numero'], 2, '0', STR_PAD_LEFT);

$html = '
<h2 style="text-align:center;"><b>CONTRATO DE ARRENDAMIENTO DE TEMPORADA</b></h2>
<p>En Cartagena, a ' . $fechaContrato . '</p>
<br>
<p style="text-align:center;"><b>REUNIDOS</b></p>
<p>De una parte, Dña. <b>HELENA BEGOÑA NAVARRO ROS</b>, mayor de edad, con DNI. 23047448-E, y con domicilio en la ciudad de CARTAGENA (MURCIA), C/Real 70 3ºB, 30201 y, Dña. <b>MARIA ROSA ROS ROSIQUE</b>, mayor de edad, con DNI 22923619-W, y con domicilio a efectos de notificaciones en la ciudad de CARTAGENA (MURCIA), C/Real 58, Segundo, 30201. Actuando conjuntamente como copropietarios de la finca y miembros de la comunidad de bienes denominada "<b>Casa Vera CB</b>.", en adelante como parte arrendadora.</p>
<br>
<p>De otra parte, la Sra. <b>' . htmlspecialchars($nombreCompleto) . '</b> mayor de edad, en su propio nombre y derecho, en lo sucesivo "el arrendatario", con número de pasaporte <b>' . htmlspecialchars($d['documento']) . '</b></p>
<p>con domicilio en <b>' . htmlspecialchars($d['direccion'] ?: '—') . '</b></p>
<br>
<p style="text-align:center;"><b>INTERVIENEN</b></p>
<p>Los Sras.</p>
<p>- HELENA BEGOÑA NAVARRO ROS</p>
<p>- MARIA ROSA ROS ROSIQUE</p>
<p>en su propio nombre y derecho, como <b>parte arrendadora</b>.</p>
<br>
<p>La Sra. <b>' . htmlspecialchars($nombreCompleto) . '</b> igualmente en su propio nombre y derecho, como <b>parte arrendataria</b> y titulares del arrendamiento de la vivienda para uso y disfrute de las habitaciones que en ella existen.</p>
<br>
<p>Reconociéndose todas las partes capacidad legal suficiente para el otorgamiento del presente contrato de arrendamiento de temporada de habitación con uso temporal y compartido de zonas comunes.</p>
<br>
<p style="text-align:center;"><b>EXPONEN</b></p>
<br>
<p><b>PRIMERO</b>.- Que la parte arrendadora es propietaria en pleno dominio de la vivienda ubicada en Paseo Delicias 23, 2º. de la ciudad de CARTAGENA (MURCIA), de 121,38 metros cuadrados de superficie divididos en 5 habitaciones y 5 cuartos de baño, tendedero y terraza, cuya superficie y composición, así como demás características son perfectamente conocidas por los intervinientes.</p>
<p>Se adjunta como anexo I al presente contrato, relación del inventario del mobiliario y contenido de la vivienda y habitaciones, incluyendo los electrodomésticos.</p>
<br>
<p><b>SEGUNDO</b>.- Que interesando a la parte arrendataria tomar en arrendamiento de una habitación de la vivienda descrita en el expositivo primero (en adelante, "el inmueble") y, estando la parte arrendadora interesada en arrendárselo, ésta lo ofrece y aquellas lo aceptan, y ambas partes convienen en celebrar el presente contrato de arrendamiento <b>de</b> <b>temporada</b> regido por las siguientes:</p>
<br>
<p style="text-align:center;"><b>ESTIPULACIONES</b></p>
<br>
<p><b>PRIMERA.- OBJETO Y DESTINO</b>.</p>
<p>Por medio del presente contrato, la parte arrendadora arrienda a la parte arrendataria una habitación del inmueble, así como sus zonas comunes, quien lo acepta en las condiciones pactadas en este documento.</p>
<p>El inmueble se arrienda como cuerpo cierto, por lo que la posible discrepancia entre la superficie real y la descrita en este contrato no afectará en más o en menos a la renta fijada en este documento.</p>
<p>El arrendatario firmará al recibir la habitación una declaración en la que conoce perfectamente la habitación y que la misma se encuentra en perfecto estado de uso y, por tanto, se compromete a devolverla en el mismo estado.</p>
<p>La habitación <b>' . $habNum . '</b> consta de:</p>
<p>m2: ...18 (aprox)...</p>
<p>Equipamiento: 1 cama de 0,90x1.90 m, 1 escritorio, 1 armario, 1 mesita de noche y 1 silla de estudio. Esta habitación cuenta con el baño interior.</p>
<p>Las partes acuerdan que el contrato es ajeno e independiente a cualquier centro o institución docente o profesional (a título enunciativo y no limitativo: universidades, centros de alto rendimiento, centros de formación profesional, centros de formación de deportistas, etc.). Por tanto, el Arrendatario vendrá obligado, en todo caso, al cumplimiento de todas sus obligaciones establecidas en el presente Contrato y, especialmente, al puntual cumplimiento de su obligación de pago de la renta, con independencia de que el curso universitario, master, postgrado, programa de formación profesional, deportiva o similar, sea interrumpido o suspendido por cualquier causa (incluso en caso de concurrir circunstancias de fuerza mayor o caso fortuito), o si los mismos son impartidos de manera no presencial.</p>
<p>La parte arrendataria se obliga a utilizar la habitación arrendada como residencia temporal durante el plazo pactado, con uso exclusivo de habitación y compartido de las zonas comunes, sin que en ningún caso constituya su domicilio habitual.</p>
<br>
<p><b>SEGUNDA.- ESTADO.</b></p>
<p>El arrendatario declara recibir el inmueble en un buen estado de uso y conservación y se comprometen a devolverlo en el mismo estado a la conclusión de la relación contractual.</p>
<p>El arrendatario deberá entregar la habitación al final del contrato en un estado conforme a su situación inicial y en un estado de mantenimiento y limpieza correspondiente a su uso normal.</p>
<p>El arrendatario es responsable de todas las degradaciones que puedan surgir durante el uso de la habitación, excluyendo las resultantes de la antigüedad, de los defectos o vicios de construcción o de fuerza mayor.</p>
<p>No está permitido realizar ninguna transformación de la habitación ni de sus equipamientos por parte del arrendatario.</p>
<p>La parte arrendadora se reserva el derecho de exigir al arrendatario la puesta de la habitación a su estado inicial con cargo al mismo en el caso de que éste hubiese realizado modificaciones.</p>
<p>Todas las obras de decoración o de remodelación que hayan sido hechas por el arrendatario quedarán en propiedad de la sociedad al término del contrato sin indemnización salvo que prefiera que se restablezca la habitación a su situación original a costa del arrendatario.</p>
<p>El arrendatario es responsable de las degradaciones sobre el mobiliario y el pavimento, así como sobre las pinturas, mobiliario y equipamiento.</p>
<p>El coste de cambio de las cerraduras o copias de llaves perdidas o rotas será a cargo del arrendatario y tendrá un coste de 20 euros por la penalización de la pérdida y la reposición.</p>
<p>La parte arrendataria aceptará sin indemnización, la ejecución de obras de partes comunes o privativas necesarias para el mantenimiento del inmueble.</p>
<br>
<p><b>TERCERA.- DURACIÓN.</b></p>
<p>El presente contrato se acuerda por la temporada comprendida entre el <b>' . $fechaInicio . '</b> y el <b>' . $fechaFin . '</b></p>
<p>El arrendatario deberá abonar al arrendador una indemnización igual a 1 mes de renta en caso de que pretenda o ejerza unilateralmente la finalización del contrato, independientemente de otras responsabilidades en que incurra por estas acciones.</p>
<p>A la salida del arrendatario, éste debe entregar la llave personalmente al arrendador o responsable que éste asignare. La habitación ha de quedar limpia y libre de basura u objetos ajenos a la composición original y se abandonará antes de las 12 horas del mediodía.</p>
<p>El arrendatario tendrá derecho preferente para renovar el contrato para el curso siguiente, siempre que lo notifique con una antelación de 2 meses a la parte arrendadora.</p>
<p>Se aplicarán a la renovación las nuevas condiciones económicas que la parte arrendadora acuerde para dicho nuevo curso y que avisará previamente al arrendatario en el caso de que éstos quieran renovar.</p>
<p>La parte arrendadora se reserva el derecho a no renovar el contrato de arrendamiento para el próximo curso si entiende que el arrendatario ha incurrido durante su estancia en mal comportamiento o en reiteradas devoluciones en el pago de las cuotas.</p>
<br>
<p><b>CUARTA.- RENTA.</b></p>
<p>La renta mensual por el alquiler de la HABITACIÓN ...<b>' . $habNum . '</b>... de la vivienda, es de <b>' . $precio . '</b>. Esta cantidad se deberá abonar por ingreso o transferencia bancaria en el número de cuenta:</p>
<p>ES75 0081 1092 1400 0153 8863</p>
<p>indicando en el concepto el número de habitación, nombre completo y mes del abono. Así mismo, se deberá enviar justificante de la transferencia dentro de ese mismo plazo al arrendador, al siguiente correo electrónico casavera30202@gmail.com</p>
<p>Estas cantidades serán pagadas por adelantado dentro de los tres primeros días de cada mes.</p>
<p>El incumplimiento de la obligación de pago o notificación del justificante del pago en el periodo fijado será motivo de resolución del contrato, dando derecho al arrendador a solicitar el desahucio, siendo por cuenta de la parte arrendataria los gastos que estas acciones originen.</p>
<p><b>Independientemente de la fecha efectiva de entrada o salida del arrendatario, se devengará en todo caso la mensualidad completa correspondiente al mes en curso, sin posibilidad de prorrateo.</b></p>
<p>El presente contrato se celebra exclusivamente para el periodo comprendido entre el <b>' . $fechaInicio . ' y el ' . $fechaFin . ' (hasta las 12 de la mañana)</b>, ambos inclusive, correspondiente al <b>segundo periodo de arrendamiento académico</b>.</p>
<p><b>No se permitirá la ocupación de la habitación más allá del ' . $fechaFinCorta . '</b> salvo que, de forma <b>excepcional</b>, <b>la habitación quedase vacante</b> y <b>la parte arrendadora autorice expresamente por escrito dicha ocupación adicional</b>, en cuyo caso se aplicará la tarifa diaria vigente.</p>
<p>En caso de ocupación anterior al <b>' . date('d/m/Y', strtotime($d['fecha_inicio'])) . '</b>, también será necesaria la autorización previa por escrito, y se aplicará igualmente la tarifa diaria correspondiente que asciende a 30 euros por noche.</p>
<p>A la finalización de la estancia, si fuera necesario a causa de mal olor en la habitación o suciedad en las cortinas o en las paredes, se cobrará en la última mensualidad un importe de 35€ en concepto de limpieza y desinfección de fin de estancia la habitación.</p>
<p>Se hace entrega en este acto del primer mes de renta, sirviendo este documento como la más eficaz carta de pago.</p>
<br>
<p><b>QUINTA.- CESIÓN Y SUBARRIENDO.</b></p>
<p>Con expresa renuncia a lo dispuesto en el artículo 32 de la LAU., el arrendatario se obliga a no subarrendar, en todo o en parte, ni ceder o traspasar el inmueble arrendado sin el consentimiento expreso y escrito del arrendador. El incumplimiento de esta cláusula será causa de resolución del contrato.</p>
<br>
<p><b>SEXTA.- DERECHO DE ADQUISICIÓN PREFERENTE</b>.</p>
<p>Con expresa renuncia de las partes a lo dispuesto en el artículo 31 de la LAU., se acuerda que en caso de venta del inmueble arrendado no tendrá el arrendatario derecho de adquisición preferente sobre el mismo. El arrendador comunicará al arrendatario con 60 días de antelación a la fecha de formalización del contrato de compraventa su intención de vender el inmueble.</p>
<br>
<p><b>SÉPTIMA.- OBRAS.</b></p>
<p>Las pequeñas reparaciones que exija el desgaste por el uso ordinario del inmueble serán de cargo del arrendador y el arrendatario estará obligado a dejar pasar a los obreros y personal capacitado que el arrendador considere para realizar dichas modificaciones. No podrá realizar la parte arrendataria ningún otro tipo de obra o modificación en el inmueble o edificio al que pertenece sin el consentimiento expreso de la parte arrendadora.</p>
<p>A pesar de no tener la consideración de obra, se prohíbe expresamente al arrendatario la realización de agujeros o perforaciones en las paredes del inmueble. La reposición de paramentos correrá por cuenta del arrendatario y no podrá compensarse el coste con la fianza depositada.</p>
<br>
<p><b>OCTAVA.- GASTOS GENERALES.</b></p>
<p><b>Todos los gastos están incluidos en el precio del alquiler</b>, incluyendo:</p>
<p>- Agua</p>
<p>- Internet por cable Ethernet.</p>
<p>&nbsp;&nbsp;&nbsp;- El uso de internet está limitado al uso doméstico razonable. No se permite el uso de programas P2P o actividades que puedan afectar al rendimiento de la red compartida.</p>
<p>- Comunidad de propietarios</p>
<p>- Impuesto sobre Bienes Inmuebles (IBI)</p>
<p>- Derramas y otros gastos extraordinarios</p>
<p>En cuanto a la electricidad, <b>se incluye un consumo mensual de hasta 100 kWh por habitación</b>. <b>En caso de superarse dicho límite en un mes determinado, el arrendatario abonará una cantidad fija de 20 € por dicho mes en concepto de exceso de consumo eléctrico.</b></p>
<br>
<p><b>NOVENA.- CLAUSULA DE PENALIZACIÓN EXPRESA.</b></p>
<p>La parte arrendataria hará entrega de las llaves del inmueble en la fecha de finalización del presente contrato. De realizar la entrega más tarde, el arrendatario abonará al arrendador la cantidad de <b>100</b> euros por cada día de retraso en la puesta a disposición de las llaves de la vivienda, en concepto de cláusula penal, además de todos los gastos que directos e indirectos que dicho retraso genere de cara a la recuperación de la vivienda.</p>
<br>
<p><b>DÉCIMA.- NORMAS DE CONVIVENCIA</b>.</p>
<p>La parte arrendataria se someterá durante toda la vigencia del contrato a las normas de la comunidad de propietarios, especialmente las relativas a la convivencia.</p>
<p>Así mismo, <b>se compromete expresamente al cuidado, buen uso y limpieza regular de las zonas comunes</b>, tales como cocina, salón, lavadero, pasillos y cualquier otro espacio compartido.</p>
<p>La parte arrendataria será responsable de mantener dichas zonas en condiciones higiénicas adecuadas, y en caso de negligencia o uso indebido, responderá de los daños o costes derivados de su reparación o limpieza.</p>
<p><b>No se permite la pernoctación de personas ajenas</b> al contrato sin autorización previa y por escrito de la parte arrendadora. El incumplimiento podrá suponer la rescisión del contrato.</p>
<p>Se prohíbe expresamente la estancia de cualquier tipo de animal en la vivienda.</p>
<p>Así mismo, será de obligado cumplimiento el anexo relativo a normas de convivencia. El no cumplimiento será motivo de rescisión inmediata del contrato.</p>
<p><b>La presencia policial o denuncia de vecinos, será motivo de resolución inmediata de contrato.</b></p>
<br>
<p><b>DÉCIMO PRIMERA.-</b></p>
<p>A la entrada y salida de la vivienda, se firmará un inventario por parte del inquilino en el que se especifica el estado de la habitación, así como los enseres y mobiliario de la misma. Se deducirán de la fianza aquellos desperfectos ocurridos durante la estancia del residente. Para ello, se recurrirá a trabajos externalizados y se irá contra factura soportada.</p>
<p>La liquidación de la habitación será constatada por el estado de la habitación, el inventario de mobiliario y la entrega de las llaves así como previa confirmación de estar corriente de pagos.</p>
<p>El arrendatario se obliga a dar a la parte arrendadora su nueva dirección antes de abandonar definitivamente la vivienda.</p>
<br>
<p><b>DÉCIMO SEGUNDA.- INCUMPLIMIENTO DE OBLIGACIONES Y RESOLUCIÓN CONTRATO</b></p>
<p>El incumplimiento por cualquiera de las partes de las obligaciones resultantes del contrato y sus anexos dará derecho a la parte que hubiere cumplido las suyas a exigir el cumplimiento de la obligación o a promover la resolución del contrato de acuerdo con lo dispuesto en el artículo 1.124 del Código Civil.</p>
<p>Además, el arrendador podrá resolver de pleno derecho el contrato por las siguientes causas:</p>
<p>a) La falta de pago de la renta o, en su caso, de cualquiera de las cantidades cuyo pago haya asumido o corresponda al arrendatario.</p>
<p>b) La falta de pago del importe de la fianza</p>
<p>c) La realización de daños causados dolosamente en la finca o de obras no consentidas por el arrendador cuando el consentimiento de éste sea necesario.</p>
<p>d) Cuando en el inmueble tengan lugar actividades molestas, insalubres, nocivas, peligrosas o ilícitas.</p>
<p><b>El arrendatario no podrá resolver anticipadamente el presente contrato. En caso de abandono de la vivienda antes de la fecha pactada de finalización, el arrendatario perderá íntegramente la fianza depositada y deberá abonar la totalidad de las rentas pendientes hasta la finalización del contrato, sin perjuicio de otras indemnizaciones que pudieran corresponder a la parte arrendadora.</b></p>
<p>En ningún caso la fianza se devolverá por desavenencias entre arrendatarios de habitaciones dobles o compartidas y que causen baja por ese motivo. Si la rescisión se produce antes de comenzar el curso, se penalizará con la fianza depositada a la firma del contrato.</p>
<p>En el caso de que el arrendatario abandonara la vivienda sin aviso previo, se le repercutirá todos los gastos y las cuotas pendientes hasta la finalización del contrato, así como el coste de los trabajos que sean necesarios realizar para devolver la habitación a su estado original y se reservará el derecho de solicitar indemnización y daños y perjuicios.</p>
<p>El arrendatario se compromete a no hacer doble juego de llaves y a devolver todas las llaves que obren en su poder al abandonar la habitación.</p>
<p>En el caso en el que el arrendatario acumulase el impago de UNA cuota del contrato de arrendamiento, el mismo se verá resuelto de manera automática, quedando legitimada la parte arrendadora a ejecutar de manera unilateral la totalidad del depósito caucional, la fianza que haya entregado por el arrendatario.</p>
<p>De la misma manera, en caso de impago que se hiciese en la cuenta del arrendatario, relativa a la renta o cualquier otro gasto o penalización derivado del arrendamiento de la habitación, el arrendador podrá repercutir todos los gastos de gestión bancaria sobre el arrendatario.</p>
<br>
<p><b>DÉCIMO TERCERA.- DOMICILIO A EFECTO DE NOTIFICACIONES.</b></p>
<p>Las partes fijan como domicilio a efectos de notificaciones derivadas de la relación contractual el que figura para cada uno de ellos en el encabezamiento del contrato. Deberán notificarse mutuamente una parte a la otra cualquier cambio que se produzca en este sentido.</p>
<p>Adicionalmente, se podrán realizar notificaciones por correo electrónico a las siguientes direcciones:</p>
<p>Arrendadores: casavera30202@gmail.com</p>
<p>Arrendatario: <b>' . htmlspecialchars($nombreCompleto) . '</b><br>Correo electrónico: <b>' . htmlspecialchars($d['email'] ?: '—') . '</b><br>TELÉFONO: <b>' . htmlspecialchars($d['telefono'] ?: '—') . '</b></p>
<br>
<p><b>DÉCIMO CUARTA.- LEGISLACIÓN APLICABLE.</b></p>
<p><b>La presente relación contractual tiene naturaleza de arrendamiento de temporada, quedando excluida del ámbito de aplicación de la Ley de Arrendamientos Urbanos (LAU) conforme al artículo 3.2 de la misma.</b></p>
<p>Se regirá por la <b>voluntad de las partes expresada en este contrato</b>, y supletoriamente por las disposiciones del <b>Código Civil</b> en materia de arrendamientos para uso distinto de vivienda.</p>
<p>El arrendatario declara que su residencia habitual se encuentra en otra localidad, y que el uso de esta habitación es <b>estrictamente temporal y vinculado al calendario académico del curso 2025/2026</b>.</p>
<br>
<p><b>DÉCIMO QUINTA.- SUMISIÓN</b>.</p>
<p>Los contratantes se someten expresamente a los Juzgados y Tribunales de la ciudad en la que se encuentra ubicado el inmueble, para todas aquellas cuestiones litigiosas que pudieran derivarse del mismo.</p>
<p>Y con el carácter expresado en la intervención, firman el presente contrato por duplicado en el lugar y fecha indicados.</p>
<br>
<p><b>DÉCIMO SEXTA.-- SISTEMA DE VIDEOVIGILANCIA</b></p>
<p>La parte arrendadora informa a la parte arrendataria de que existen <b>cuatro cámaras de videovigilancia instaladas</b> en las zonas comunes del inmueble: <b>dos ubicadas en la cocina-salón, una en la entrada y otra en el lavadero</b>, con el único fin de <b>garantizar la seguridad de las instalaciones y la convivencia entre los residentes</b>.</p>
<p>Dichas cámaras <b>no graban ni captan audio en zonas privadas</b> como habitaciones o baños, ni invaden la intimidad personal de los arrendatarios. Las ubicaciones han sido debidamente señalizadas conforme a la normativa vigente.</p>
<p>La parte arrendataria declara haber sido informada previamente de su existencia, ubicación y finalidad, y consiente expresamente su instalación y funcionamiento conforme al <b>Reglamento (UE) 2016/679 (RGPD)</b> y la <b>Ley Orgánica 3/2018 de Protección de Datos Personales y garantía de los derechos digitales</b>.</p>
<p>Las imágenes grabadas, en caso de existir, se conservarán durante un plazo máximo de 30 días y únicamente podrán ser consultadas por la parte arrendadora o por las autoridades competentes, en caso de incidente grave o requerimiento legal.</p>
<br>
<p>En Cartagena a ' . $fechaContrato . '.</p>
<br><br>
<table border="0" cellpadding="10" style="width:100%;">
<tr>
  <td width="50%" style="text-align:center;">
    <p>_______________________________</p>
    <p><b>ARRENDADOR/A</b></p>
    <p>Helena Begoña Navarro Ros</p>
    <p>En representación de Casa Vera C.B.</p>
  </td>
  <td width="50%" style="text-align:center;">
    <p>_______________________________</p>
    <p><b>ARRENDATARIO/A</b></p>
    <p>' . htmlspecialchars($nombreCompleto) . '</p>
    <p>Pasaporte/DNI: ' . htmlspecialchars($d['documento']) . '</p>
  </td>
</tr>
</table>
';

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('contrato_' . $habNum . '_' . strtolower(str_replace(' ', '_', $d['apellidos'])) . '.pdf', 'D');