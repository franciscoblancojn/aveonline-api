<?php
class AVE_API_WP_JSON
{
    const LIST_TRANSPORTADORA = [
        1028,
        1009,
        29,
        1031,
        1016,
        33,
        1010
    ];

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
        register_rest_route('ave', '/cotizar-paralelo', [
            'methods'  => 'POST',
            'callback' => [self::class, 'postCotizarParalelo'],
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
                "idagente" => "24246",

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
    public static function postCotizarParalelo($request)
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

            // =========================
            // DATOS REQUEST
            // =========================

            $origen = $request->get_param('origen');
            $destino = $request->get_param('destino');

            $contraentrega = $request->get_param('contraentrega') ? 1 : 0;

            $peso = (float) $request->get_param('peso');
            $unidades = (int) $request->get_param('unidades');

            $valorDeclarado = (float) $request->get_param('valor_declarado');

            $alto = $request->get_param('alto');
            $largo = $request->get_param('largo');
            $ancho = $request->get_param('ancho');

            // =========================
            // PRODUCTO
            // =========================

            $producto = [
                "name" => "Producto",
                "peso" => $peso,
                "unidades" => $unidades,
                "valorDeclarado" => $valorDeclarado
            ];

            if ($alto !== null && $alto !== '') {
                $producto["alto"] = (float) $alto;
            }

            if ($largo !== null && $largo !== '') {
                $producto["largo"] = (float) $largo;
            }

            if ($ancho !== null && $ancho !== '') {
                $producto["ancho"] = (float) $ancho;
            }

            // =========================
            // BODY BASE
            // =========================

            $baseBody = [
                "tipo" => "cotizarDoble",
                "access" => "",
                "token" => $token,
                "idempresa" => "24",
                "idagente" => "24246",

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

            // =========================
            // CURL MULTI
            // =========================

            $multiHandle = curl_multi_init();

            $curlHandles = [];

            $url = "https://app.aveonline.co/api/nal/v1.0/generarGuiaTransporteNacional.php";

            foreach (self::LIST_TRANSPORTADORA as $idtransportador) {

                $body = $baseBody;

                $body["idtransportador"] = $idtransportador;

                $ch = curl_init($url);

                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => json_encode($body),
                    CURLOPT_HTTPHEADER => [
                        'Content-Type: application/json',
                        'Accept: application/json'
                    ],
                    CURLOPT_TIMEOUT => 20,
                    CURLOPT_CONNECTTIMEOUT => 10,
                ]);

                curl_multi_add_handle($multiHandle, $ch);

                $curlHandles[$idtransportador] = $ch;
            }

            // =========================
            // EJECUTAR PARALELO
            // =========================

            $running = null;

            do {

                curl_multi_exec($multiHandle, $running);

                curl_multi_select($multiHandle);
            } while ($running > 0);

            // =========================
            // RESPUESTAS
            // =========================

            $cotizaciones = [];

            $status = "ok";

            $message = "cotizaciones encontradas";

            foreach ($curlHandles as $idtransportador => $ch) {

                $response = curl_multi_getcontent($ch);

                $error = curl_error($ch);

                if ($error) {

                    $cotizaciones[] = [
                        "numbererror" => "999",
                        "dataerror" => $error,
                        "codTransportadora" => (string)$idtransportador,
                        "nombreTransportadora" => "ERROR CURL",
                        "logoTransportadora" => "000",
                        "logoTransportadora2" => null,
                        "origen" => "000",
                        "destino" => "000",
                        "unidades" => "000",
                        "kilos" => "000",
                        "pesovolumen" => "000",
                        "valoracion" => "000",
                        "porcentajeValoracion" => "000",
                        "codigoTrayecto" => "000",
                        "trayecto" => "000",
                        "tipoEnvio" => "000",
                        "fletexkilo" => "000",
                        "fletexunidad" => "000",
                        "fletetotal" => "000",
                        "diasentrega" => "000",
                        "costoManejo" => 0,
                        "valorTotal" => 0,
                        "valorOtrosRecaudos" => 0,
                        "total" => 0,
                        "contraentrega" => false
                    ];
                } else {

                    $data = json_decode($response, true);

                    if (
                        isset($data['cotizaciones']) &&
                        is_array($data['cotizaciones'])
                    ) {

                        foreach ($data['cotizaciones'] as $cotizacion) {

                            $cotizaciones[] = $cotizacion;
                        }
                    } else {

                        $cotizaciones[] = [
                            "numbererror" => "999",
                            "dataerror" => "Respuesta inválida",
                            "codTransportadora" => (string)$idtransportador,
                            "nombreTransportadora" => "ERROR API",
                            "logoTransportadora" => "000",
                            "logoTransportadora2" => null,
                            "origen" => "000",
                            "destino" => "000",
                            "unidades" => "000",
                            "kilos" => "000",
                            "pesovolumen" => "000",
                            "valoracion" => "000",
                            "porcentajeValoracion" => "000",
                            "codigoTrayecto" => "000",
                            "trayecto" => "000",
                            "tipoEnvio" => "000",
                            "fletexkilo" => "000",
                            "fletexunidad" => "000",
                            "fletetotal" => "000",
                            "diasentrega" => "000",
                            "costoManejo" => 0,
                            "valorTotal" => 0,
                            "valorOtrosRecaudos" => 0,
                            "total" => 0,
                            "contraentrega" => false
                        ];
                    }
                }

                curl_multi_remove_handle($multiHandle, $ch);

                curl_close($ch);
            }

            // =========================
            // ORDENAR POR PRECIO
            // =========================
            usort($cotizaciones, function ($a, $b) {

                $aError = $a['numbererror'] !== '-0-';
                $bError = $b['numbererror'] !== '-0-';

                // válidos primero
                if ($aError !== $bError) {
                    return $aError ? 1 : -1;
                }

                return ($a['total'] ?? 999999999)
                    <=>
                    ($b['total'] ?? 999999999);
            });

            return [
                'success' => true,
                'data' => [
                    'status' => $status,
                    'message' => $message,
                    'cotizaciones' => $cotizaciones
                ]
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
