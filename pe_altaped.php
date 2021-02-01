<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        .producto{
            width: 48%;
            height: 50px;
            border-bottom: 0.5px gray solid;
        }

        .producto input[type="number"]{
            height: 25px;
            margin: 10px 20px 10px 5px;
            width: 50px;
        }

        .itempedido{
            float: left;
        }

        .cantidad{
            float: left;
        }

        .producto select{
            height: 30px;
            width: 500px;
            margin: 10px 20px 10px 5px;
        }

        #formulario{
            display: inline;
            width: 48%;
            left: 10px;
        }

        .imgMas{
            width: 40px;
            float: right;
        }

        .imgMas:hover{
            cursor:pointer;
        }

        .producto>img:hover{
            cursor: pointer;
        }

        .carro{
            width: 48%;
        }
    </style>
    <title>Realizar pedidos</title>
</head>
<body>
    <h2>Realiza tu pedido</h2>
    <form action="pe_altaped.php" method="REQUEST" name="nuevoPedido" id="formulario">
        <div class="producto">
            <div class="itempedido">
                <label for="producto" class="labelproducto">Producto: </label><select name="producto" id="producto">
                    <?php
                        include 'funciones.php';
                        llenarOpciones();
                    ?>
                </select>
            </div>
            <div class="cantidad">
                <label for="cantidad1" class="labelcantidad">Cantidad: </label><input type="number" name="cantidad" id="cantidad" min="1" value="1">
            </div>
        </div><br><br>
        <label for="checkNumber">Check number: </label><input type="text" name="checkNumber" id="checkNumber"><br><br>
        <label for="customerNumber">Customer number: </label><input type="text" name="customerNumber" id="customerNumber"><br><br>
        <input type="submit" value="Añadir" name="anadir">&nbsp;&nbsp;<input type="submit" value="Vaciar Carro" name="vaciar">&nbsp;&nbsp;<input type="submit" value="Finalizar compra" name="comprar">
    </form>
    <div class="carro">
        <?php
            whichAction();
            mostrarCarro();
        ?>
    </div>
</body>
</html>