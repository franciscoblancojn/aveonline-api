<?php
class AVE_API_WP_JSON
{
    public static function init()
    {
        register_rest_route('ave/city', '/search', [
            'methods'  => 'GET',
            'callback' => [self::class, 'getCity'],
            'permission_callback' => '__return_true',
        ]);
        register_rest_route('ave', '/cotizar', [
            'methods'  => 'POST',
            'callback' => [self::class, 'postCotizar'],
            'permission_callback' => '__return_true',
        ]);
    }

    public static function getCity($request)
    {
        $search = $request->get_param('search');

        try {
            $ch = curl_init("https://app.aveonline.co/api/comunes/v1.0/ciudad.php");

            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode([
                    'tipo' => 'listar',
                    'data' => $search,
                    'registros' => 100,
                ]),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Accept: application/json',
                ],
                CURLOPT_TIMEOUT => 20,
            ]);

            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                return [
                    'success' => false,
                    'message' => curl_error($ch)
                ];
            }

            curl_close($ch);

            $data = json_decode($response, true);

            return [
                'success' => true,
                'data' => $data['ciudades'] ?? []
            ];
        } catch (\Throwable $e) {

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
    public static function postCotizar($request)
    {
        try {
            $CONFIG = get_option(AVE_API_KEY, []);
            $token = $CONFIG['token'] ?? null;

            if (!$token) {
                return [
                    'success' => false,
                    'message' => 'Token no configurado'
                ];
            }

            // 🔹 Obtener datos del request
            $origen = $request->get_param('origen');
            $destino = $request->get_param('destino');
            $contraentrega = $request->get_param('contraentrega') ? 1 : 0;
            $peso = (float) $request->get_param('peso');
            $unidades = (int) $request->get_param('unidades');
            $valorDeclarado = (float) $request->get_param('valor_declarado');

            $alto = $request->get_param('alto');
            $largo = $request->get_param('largo');
            $ancho = $request->get_param('ancho');

            $producto = [
                "name" => "Producto",
                "peso" => $peso,
                "unidades" => $unidades,
                "valorDeclarado" => $valorDeclarado
            ];

            // solo agregar dimensiones si existen
            if ($alto !== null && $alto !== '') {
                $producto["alto"] = (float) $alto;
            }

            if ($largo !== null && $largo !== '') {
                $producto["largo"] = (float) $largo;
            }

            if ($ancho !== null && $ancho !== '') {
                $producto["ancho"] = (float) $ancho;
            }
            $body = [
                "tipo" => "cotizarDoble",
                "access" => "",
                "token" => $token,
                "idempresa" => "24",

                "origen" => $origen,
                "destino" => $destino,

                "idasumecosto" => 1,
                "contraentrega" => $contraentrega,
                "contraentregaPayment" => $contraentrega,

                "valorrecaudo" => $contraentrega ? $valorDeclarado : 0,

                "productos" => [
                    $producto
                ],

                "valorMinimo" => 0,
                "plugin" => "wordpress"
            ];

            // 🔹 CURL
            $ch = curl_init("https://app.aveonline.co/api/nal/v1.0/generarGuiaTransporteNacional.php");

            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($body),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Accept: application/json'
                ],
                CURLOPT_TIMEOUT => 20,
            ]);

            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                return [
                    'success' => false,
                    'message' => curl_error($ch)
                ];
            }

            curl_close($ch);

            $data = json_decode($response, true);

            return [
                'success' => true,
                'data' => $data
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}

add_action('rest_api_init', ['AVE_API_WP_JSON', 'init']);
