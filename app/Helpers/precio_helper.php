<?php
use App\Models\PrecioEscaladoModel;
use App\Models\ProductoModel;

function precio_por_cantidad(int $productoId, int $cantidad): float
{
    $escalaModel = new PrecioEscaladoModel();
    $escala = $escalaModel->getMejorEscala($productoId, $cantidad);
    if ($escala) return (float)$escala['precio'];

    $producto = (new ProductoModel())->select('precio_base')->find($productoId);
    return $producto ? (float)$producto['precio_base'] : 0.0;
}