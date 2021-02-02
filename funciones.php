<?php

session_start();

function connBDD(){
    /*
    Función hecha por Carlos Sánchez 
    Descripción: Función para establecer la conexión con la base de datos

    Parámetros de entrada: No
    Parámetros de salida: $conn(mysqli_connect)
    */
    $nombreserver = 'localhost';
    $usuario = 'carloscopy';
    $password = 'rootroot';
    $nombreBBDD = 'pedidos';
    $conn = mysqli_connect($nombreserver, $usuario, $password, $nombreBBDD);

    return $conn;
}

function getInventarioTotal(){
    /*
    Función hecha por Carlos Sánchez 
    Descripción: Devuelve un array con todos los objetos del inventario cuyo stock es mayor que 0

    Parámetros de entrada: No
    Parámetros de salida:$array[[$fila],[$fila],[$fila],...]
    */
    $array = Array();
    $sqlQuery = "SELECT productCode as 'id', productName as 'nombre', quantityInStock as 'cantidad', buyPrice as 'precio' FROM products WHERE quantityInStock >= 0;";
    $result = mysqli_query(connBDD(), $sqlQuery);
    if($result){
        if($result->num_rows > 0){
            while($fila = $result->fetch_assoc()){
                array_push($array, $fila);
            }
        }
    }
    return $array;
}

function anadirAlCarro($producto, $cantidad){
    /*
    Función hecha por Carlos Sánchez 
    Descripción: Añade un producto de la lista tanto al array de la session que utilizaremos para buscar dentro de la base de datos e insertar como el que utilizaremos para mostrar el carrito.

    Parámetros de entrada: String $producto (ID - NOMBRE), Number $cantidad;
    Parámetros de salida: No
    */
    if(!isset($_SESSION['carroVisible']) || !isset($_SESSION['arrayCarro'])){
        $_SESSION['arrayCarro'] = Array();
        $_SESSION['carroVisible'] = Array();
    }
    $productoArray = Array();
    array_push($productoArray, nombreDeProducto($producto), $cantidad);
    array_push($_SESSION['carroVisible'], $productoArray);
    $productoArray = Array();
    array_push($productoArray, idDeProducto($producto), $cantidad);
    array_push($_SESSION['arrayCarro'], $productoArray);
}

function idDeProducto($producto){
    /*
    Función hecha por Carlos Sánchez 
    Descripción: Devuelve el ID del producto sin el nombre ni espacios

    Parámetros de entrada: $producto(ID - NOMBRE PRODUCTO)
    Parámetros de salida: String(ID)
    */
    return trim(strtok($producto, '-'));
}

function nombreDeProducto($producto){
    /*
    Función hecha por Carlos Sánchez 
    Descripción: Devuelve el nombre del producto sin el ID ni espacios

    Parámetros de entrada: $producto(ID - NOMBRE PRODUCTO)
    Parámetros de salida: String(NOMBRE PRODUCTO)
    */
    strtok($producto, '-');
    return trim(strtok('-'));
}

function mostrarCarro(){
    /*
    Función hecha por Carlos Sánchez 
    Descripción: Imprime por pantalla en formato tabla el contenido del carrito si hay algo dentro

    Parámetros de entrada:No
    Parámetros de salida:No
    */
    if(isset($_SESSION['carroVisible']) && count($_SESSION['carroVisible'])>0){
        echo "<h1>Carrito</h1><table border='1'><tr><td>Nombre producto</td><td>Cantidad</td></tr>";
        for ($i=0; $i < count($_SESSION['carroVisible']); $i++) { 
            echo "<tr>";
            for ($j=0; $j < count($_SESSION['carroVisible'][$i]); $j++) { 
                echo "<td>".$_SESSION['carroVisible'][$i][$j]."</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }
}

function whichAction(){
    /*
    Función hecha por Carlos Sánchez 
    Descripción: Detecta qué botón se ha pulsado o si no se ha pulsado ninguno y ejecuta la consecuente función en cada caso

    Parámetros de entrada:No
    Parámetros de salida:No
    */
    if(isset($_REQUEST['anadir'])){
        anadirAlCarro($_REQUEST['producto'], $_REQUEST['cantidad']);
    }else if(isset($_REQUEST['vaciar'])){
        vaciarCarro();
    }else if(isset($_REQUEST['comprar'])){
        hacerCompra();
    }
}

function llenarOpciones(){
    /*
    Función hecha por Carlos Sánchez 
    Descripción: Imprime todas las etiquetas <option> con los productos del inventario

    Parámetros de entrada:No
    Parámetros de salida:No
    */
    $inventarioTotal = getInventarioTotal();
    for ($i=0; $i < count($inventarioTotal); $i++) {
        echo "<option>".$inventarioTotal[$i]['id']." - ".$inventarioTotal[$i]['nombre']."</option>";
    }
}

function vaciarCarro(){
    /*
    Función hecha por Carlos Sánchez 
    Descripción: Vacía todas las variables de sesión
    Parámetros de entrada:No
    Parámetros de salida:No
    */
    session_unset();
}

function comprobarCheckNumber($checknumber){
/*
    Función hecha por Carlos Sánchez 
    Descripción:Comprueba que el checkNumber está en el formato correcto

    Parámetros de entrada: String $checknumber
    Parámetros de salida: Boolean True or False
    */

    $expReg = '/^[A-Za-z]2[0-9]5$/';
    return preg_match($expReg, $checknumber);
}

function hacerCompra(){

    /*
    Función hecha por Carlos Sánchez 
    Descripción: Construye la consulta para insertar el registro del pedido, los detalles del pedido, el pago y resta los productos del inventario

    Parámetros de entrada: No
    Parámetros de salida: No
    */
    /*
    $totalprecio = 0.00;
    if(checkCheckNumber($_REQUEST['checkNumber']) && checkCustomerNumber($_REQUEST['customerNumber'])){ 
        $sqlQuery = "INSERT INTO orders(orderNumber, orderDate, requiredDate, shippedDate, status, comments, customerNumber) VALUES (".(getOrderNumber()).", '".date("y/m/d")."', '".date("y/m/d")."', null, 'In Process', null, ".$_REQUEST['customerNumber']."); INSERT INTO orderdetails(orderNumber, productCode, quantityOrdered, priceEach, orderLineNumber) VALUES ";
        echo "<script>alert(\"Consulta SQL: $sqlQuery\")</script>";
        for ($i=0; $i < count($_SESSION['arrayCarro']); $i++) {
            if($i == 0){
                $sqlQuery.= "(".getOrderNumber();
            }else{
                $sqlQuery.= ", (".getOrderNumber();
            }
            for ($j=0; $j < count($_SESSION['arrayCarro'][$i]); $j++) {
                if($j == 0){
                    $sqlQuery.=", '".$_SESSION['arrayCarro'][$i][$j]."'";
                    $precio = getPrecio($_SESSION['arrayCarro'][$i][$j]);
                }else{
                    $sqlQuery.=", ".$_SESSION['arrayCarro'][$i][$j];
                    $cantidad = $_SESSION['arrayCarro'][$i][$j];
                }
            }
            $sqlQuery.=", $precio, 1)";
            $totalprecio+=$precio*$cantidad;
        }
        echo "<script>alert(\"Consulta SQL: $sqlQuery\")</script>";
        $sqlQuery.="; INSERT INTO payments(customerNumber, checkNumber, paymentDate, amount) VALUES (".$_REQUEST['customerNumber'].", '".$_REQUEST['checkNumber']."', '".date("y/m/d")."', $totalprecio); ";
        echo "<script>alert(\"Consulta SQL: $sqlQuery\")</script>";
        
        for ($i=0; $i < count($_SESSION['arrayCarro']); $i++) {
            if($i == 0){
                $sqlQuery.= "UPDATE products SET quantityInStock = quantityInStock - ".$_SESSION['arrayCarro'][$i][1]." WHERE productCode = '".$_SESSION['arrayCarro'][$i][0]."';";
            }
        }

        echo "<script>alert(\"Consulta SQL: $sqlQuery\")</script>";
        $result = mysqli_query(connBDD(), $sqlQuery);
        if($result->affected_rows == 0){
            die("Error en la compra: $result->affected_rows");
        }
    }
    */
    $sqlQuery1 = "INSERT INTO orders(orderNumber, orderDate, requiredDate, shippedDate, status, comments, customerNumber) VALUES (10433, '21/02/01', '21/02/01', null, 'In Process', null, 103);";

    $result = mysqli_query(connBDD(), $sqlQuery1);
    echo "<script>alert('Query 1: $result')</script>";
    $sqlQuery2 = "INSERT INTO orderdetails(orderNumber, productCode, quantityOrdered, priceEach, orderLineNumber) VALUES (10433, 'S10_1678', 1, 48.81, 1), (10433, 'S10_1949', 1, 98.58, 1);";

    $result = mysqli_query(connBDD(), $sqlQuery2);
    echo "<script>alert('Query 2: $result')</script>";
    $sqlQuery3 = "INSERT INTO payments(customerNumber, checkNumber, paymentDate, amount) VALUES (103, 'AA00010', '21/02/01', 147.39); ";

    $result = mysqli_query(connBDD(), $sqlQuery3);
    echo "<script>alert('Query 3: $result')</script>";
    $sqlQuery4 = "UPDATE products SET quantityInStock = quantityInStock - 1 WHERE productCode = 'S10_1678'";

    $result = mysqli_query(connBDD(), $sqlQuery4);
    echo "<script>alert('Query 4: $result')</script>";
    $sqlQuery5 = "UPDATE products SET quantityInStock = quantityInStock - 1 WHERE productCode = 'S10_1949'";

    $result = mysqli_query(connBDD(), $sqlQuery5);
    echo "<script>alert('Query 5: $result')</script>";
    
    
}

function checkCustomerNumber($customerNumber){
    /*
    Función hecha por Carlos Sánchez 
    Descripción: Comprueba que un customerNumber existe en la base de datos

    Parámetros de entrada: Number $customerNumber(999)
    Parámetros de salida: Boolean True or False
    */
    $sqlQuery = "SELECT * FROM customers WHERE customerNumber = $customerNumber";
    $result = mysqli_query(connBDD(), $sqlQuery);

    if(!$result || $result->num_rows < 1){
        return false;
    }else{
        return true;
    }
    
}

function checkCheckNumber($checkNumber){
    /*
    Función hecha por Carlos Sánchez 
    Descripción: Comprueba que el checkNumber NO está en la BDD

    Parámetros de entrada: String $checkNumber
    Parámetros de salida: Boolean True or False
    */
    $sqlQuery = "SELECT * FROM payments WHERE checkNumber = '$checkNumber'";
    $result = mysqli_query(connBDD(), $sqlQuery);

    if($result->num_rows > 0){
        return false;
    }else{
        return true;
    }
}

function getOrderNumber(){
    /*
    Función hecha por Carlos Sánchez 
    Descripción: Obtiene un número de pedido 1 unidad mayor que el más alto de la base de datos y lo devuelve

    Parámetros de entrada:
    Parámetros de salida: Number $numero
    */
    $sqlQuery = "SELECT MAX(orderNumber) as 'orderNumber' FROM orders";
    $result = mysqli_query(connBDD(), $sqlQuery);

    $numero = $result->fetch_assoc()['orderNumber'] + 1;
    return $numero;
}

function getPrecio($idProducto){
    /*
    Función hecha por Carlos Sánchez 
    Descripción: Obtiene el precio de un producto y lo devuelve

    Parámetros de entrada: String $idProducto(A99_9999)
    Parámetros de salida: Number $precio(99.99)
    */
    $sqlQuery = "SELECT buyPrice FROM products WHERE productCode = '$idProducto'";
    $result = mysqli_query(connBDD(), $sqlQuery);
    $precio = $result->fetch_assoc()['buyPrice'];
    return $precio;
}

function obtenerIDs(){

    # Función 'obtenerClientes'. 
    # Parámetros: 
    # 	- none
    # Funcionalidad:
    # Obtener todas las id de los clientes
    #
    # Retorna: Todas los clientes
    #
    # Código por Edu Gutierrez
    
    global $conexion;
    try {
        $consulta = $conexion->prepare("SELECT customerNumber FROM customers ORDER BY customerNumber ASC");
        $consulta->execute();
        $datos = $consulta -> fetchAll(PDO::FETCH_ASSOC);
        return !empty($datos)? $datos: null;
    } catch (PDOException $ex) {
        echo "<p>Ha ocurrido un error al devolver los datos de los clientes: <span style='color: red; font-weight: bold;'>". $ex->getMessage()."</span></p></br>";
        return null;
    }

}

function obtenerInfoPedidos($id){

    # Función 'obtenerInfoPedidos'. 
    # Parámetros: 
    # 	- $id (customerNumber)
    # Funcionalidad:
    # Obtener los información de pedidos de ese cliente.
    #
    # Retorna: Los datos de pedidos realizados del Cliente / 
    #(customerNumber), (orderNumber), (orderDate), (status), (orderLineNumber), (productName), (quantityOrdered) y (priceEach). 
    # Código por Edu Gutierrez
    
    global $conexion;
    try {
        $consulta = $conexion->prepare("SELECT customerNumber, orders.orderNumber, orderDate, status, orderLineNumber, productName, quantityOrdered, priceEach FROM orders LEFT JOIN orderdetails ON 
        orders.orderNumber=orderdetails.orderNumber LEFT JOIN products ON products.productCode=orderdetails.productCode  WHERE customerNumber=:id ORDER BY orderLineNumber ASC");
        $consulta->bindParam(":id",$id);
        $consulta->execute();
        $datos = $consulta -> fetchAll(PDO::FETCH_ASSOC);
        return !empty($datos)? $datos: null;
    } catch (PDOException $ex) {
        echo "<p>Ha ocurrido un error al devolver los datos del cliente que se busca por este id: <span style='color: red; font-weight: bold;'>". $ex->getMessage()."</span></p></br>";
        return null;
    }

}

function imprimirInfoPedidos($infos){


    # Función 'imprimirInfoPedidos'. 
    # Parámetros: 
    # 	- $infos (Información de pedidos)
    # Funcionalidad:
    # Imprimir en una tabla la información de los pedidos
    #
    # Retorna: none
    #
    # Código por Edu Gutierrez
    
    echo "<br>En total hay ".count($infos)." pedidos para el cliente ".$infos[0]["customerNumber"].":<br><br>";

    echo 	"<table border='1'>
    <tr>
        <th>Número de Línea</th>
        <th>Número Pedido</th>
        <th>Fecha Pedido</th>
        <th>Estado Pedido</th>
        <th>Nombre Producto</th>
        <th>Cantidad Pedida</th>
        <th>Precio Unidad</th>
    </tr>";

foreach ($infos as $info) {
echo "<tr>
        <td>". $info["orderLineNumber"] ."</td>
        <td>". $info["orderNumber"] ."</td>
        <td>". $info["orderDate"] ."</td>
        <td>". $info["status"] ."</td>
        <td>". $info["productName"] ."</td>
        <td>". $info["quantityOrdered"] ."</td>
        <td>". $info["priceEach"] .' €'."</td>
     </tr>";
}
echo 	"</table>";
}

function obtenerLineasProductos(){

    # Función 'obtenerLineasProductos'. 
    # Parámetros: 
    # 	- none
    # Funcionalidad:
    # Obtener todas las líneas de producto
    #
    # Retorna: Todas las líneas de producto
    #
    # Código por Edu Gutierrez
    
    global $conexion;
    try {
        $consulta = $conexion->prepare("SELECT productLine FROM productLines ORDER BY productLine ASC");
        $consulta->execute();
        $datos = $consulta -> fetchAll(PDO::FETCH_ASSOC);
        return !empty($datos)? $datos: null;
    } catch (PDOException $ex) {
        echo "<p>Ha ocurrido un error al devolver los datos de las líneas de producto: <span style='color: red; font-weight: bold;'>". $ex->getMessage()."</span></p></br>";
        return null;
    }
}

function verStockLineaProducto($linea_prod){

    # Función 'verStockLineaProducto'. 
    # Parámetros: 
    # 	- $linea_prod (productLine)
    # Funcionalidad:
    # Obtener el stock de una determinada línea de producto.
    #
    # Retorna: Stock total 
    # Código por Edu Gutierrez
    
    global $conexion;
    try {
        $consulta = $conexion->prepare("SELECT productLine ,productName, quantityInStock FROM products WHERE productLine=:linea_prod");
        $consulta->bindParam(":linea_prod",$linea_prod);
        $consulta->execute();
        $datos = $consulta -> fetchAll(PDO::FETCH_ASSOC);
        return !empty($datos)? $datos: null;
    } catch (PDOException $ex) {
        echo "<p>Ha ocurrido un error al devolver los datos del cliente que se busca por esta línea  de producto: <span style='color: red; font-weight: bold;'>". $ex->getMessage()."</span></p></br>";
        return null;
    }

}


function imprimirStockLineaProducto($infos){

     # Función 'imprimirStockLineaProducto'. 
    # Parámetros: 
    # 	- $infos (Información de pedidos)
    # Funcionalidad:
    # Imprimir en una tabla las cantidades de productos.
    #
    # Retorna: none
    #
    # Código por Edu Gutierrez
    
    $stockTotal=0;

    echo 	"<p>Stock Línea de Producto: ". $infos[0]["productLine"] ."<p><table border='1'>
    <tr>
        <th>Nombre de producto</th>
        <th>Cantidad en Stock</th>
    </tr>";

foreach ($infos as $info) {
echo "<tr>
        <td>". $info["productName"] ."</td>
        <td>". $info["quantityInStock"] ."</td>
     </tr>";
     $stockTotal+=$info["quantityInStock"];
}
echo 	"</table><p>El Stock total es ".$stockTotal." unidades.</p>";
}


function consultarPagos($id, $fecha_inicio, $fecha_fin){
    # Función 'consultarPagos'. 
    # Parámetros: 
    # 	- $id (customerNuber)
    #	- $fecha_inicio (fecha de inicio, desde la cual se empieza a buscar en el historial)
    #	- $fecha_fin (fecha de fin, desde la cual se termina de buscar en el historial)
    #
    # Funcionalidad:
    # Obtiene las pagos relizadas por un cliente entre dos fechas.
    #
    # Retorna: Los fechas y cantidades de esos pagos sino retorna null
    #
    # Código por Edu Gutierrez

    
        global $conexion;

        if($fecha_inicio==null) $fecha_inicio="2000-01-01"; 
        if ($fecha_fin==null) $fecha_fin=date("Y-m-d");

        if ($fecha_inicio>$fecha_fin) {

            $aux=$fecha_inicio;
            $fecha_inicio=$fecha_fin;
            $fecha_fin=$aux;

        }  
        try {

            $consulta = $conexion->prepare("SELECT paymentDate,amount FROM payments WHERE customerNumber = :id and (paymentDate >= :fechaInicio and paymentDate <= :fechaFin) ORDER BY paymentDate DESC");
            $consulta->bindParam(":fechaInicio", $fecha_inicio);
            $consulta->bindParam(":fechaFin", $fecha_fin);
            $consulta->bindParam(":id",$id);
            $consulta->execute();
    
            $datos=$consulta->fetchAll(PDO::FETCH_ASSOC);
            $fechas=array("fechainicio"=>$fecha_inicio,"fechafin"=>$fecha_fin);

            $respuesta=array("datos"=>$datos, "fechas"=>$fechas);
            return !empty($respuesta["datos"])? $respuesta: null;

        } catch(PDOException $ex) {

            echo "<p>Ha ocurrido un error al devolver los pagos que ha realizado este cliente: <span style='color: red; font-weight: bold;'>". $ex->getMessage()."</span></p></br>";
            return null;
        } 
    }

    function imprimirPagos($infos, $id){

   # Función 'imprimirPagos'. 
    # Parámetros: 
    # 	- $infos (Información de los pagos)
    # Funcionalidad:
    # Imprimir en una tabla las fechas y cantidades de los pagos de un cliente..
    #
    # Retorna: none
    #
    # Código por Edu Gutierrez
    
 

    echo 	"<p>Pagos entre: ". $infos["fechas"]["fechainicio"]  ." // "
    . $infos["fechas"]["fechafin"]. " del cliente Nº ". $id ." <p><table border='1'>
    <tr>
        <th>Fecha de Pago</th>
        <th>Cantidad</th>
    </tr>";

foreach ($infos["datos"] as $info) {
echo "<tr>
        <td>". $info["paymentDate"] ."</td>
        <td>". $info["amount"] ." €</td>
     </tr>";
     
   }
}

?>
