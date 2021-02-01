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

?>