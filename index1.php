<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" type="image/png" href="assets/img/favicon.ico">
    <title>TEUCARD - GERADOR</title>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css">
    <link href="https://fonts.googleapis.com/css?family=Poppins:200,300,400,600,700,800" rel="stylesheet" />
    <link href="assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="assets/css/style.css?v=1.0.0" rel="stylesheet" />
    <link href="assets/demo/demo.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <style>
        .card {
            border-radius: 30px;
        }
        .form-control, .btn {
            border-radius: 20px;
        }
        .btn-play {
            padding: 10px 20px;
        }
        .result {
            margin-top: 20px;
        }
        textarea {
            width: 100%;
            height: 200px;
            border-radius: 10px;
            padding: 10px;
            margin-bottom: 10px;
        }
        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
            }
            .form-control {
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="col-md-11 mt-4" style="margin: auto;">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body text-center">
                        <h4 class="title mb-2"><img class="logo" width="19%" src=""></h4>
                        <form method="post" action="">
                            <div class="form-row">
                                <div class="col-md-4 mb-2">
                                    <input type="text" name="bin" class="form-control" placeholder="Digite o BIN:" required pattern="[0-9x]{6,16}" title="BIN deve conter entre 6 e 16 dígitos ou 'x'">
                                </div>
                                <div class="col-md-2 mb-2">
                                    <input type="number" name="mes" class="form-control" placeholder="Mês" min="1" max="12" required>
                                </div>
                                <div class="col-md-2 mb-2">
                                    <input type="number" name="ano" class="form-control" placeholder="Ano" min="<?php echo date('Y'); ?>" required>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <input type="number" name="qtd" class="form-control" placeholder="Quantidade de Cartões" min="1" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-play text-white"><i class="fa fa-play"></i> GERAR</button>
                        </form>
                        <br>
                        <?php
                        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                            $bin = $_POST['bin'];
                            $mes = $_POST['mes'];
                            $ano = $_POST['ano'];
                            $qtd = $_POST['qtd'];

                            // Função para exibir notificação de erro
                            function show_error_notification($message) {
                                echo "<script>
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Erro',
                                        text: '$message'
                                    });
                                </script>";
                            }

                            // Validação do BIN
                            if (!preg_match('/^[0-9x]{6,16}$/', $bin)) {
                                show_error_notification('BIN inválido. Deve conter entre 6 e 16 dígitos ou "x".');
                            } elseif ($mes < 1 || $mes > 12) {
                                show_error_notification('Mês inválido. Deve estar entre 1 e 12.');
                            } elseif ($ano < date('Y')) {
                                show_error_notification('Ano inválido. Deve ser o ano atual ou futuro.');
                            } elseif ($qtd < 1) {
                                show_error_notification('Quantidade inválida. Deve ser pelo menos 1.');
                            } else {
                                $cards = generate_cards($bin, $qtd, $mes, $ano);
                                display_data_with_style($cards);
                            }
                        }

                        function generate_cards($bin, $qtd, $mes, $ano) {
                            $cards = [];
                            for ($i = 0; $i < $qtd; $i++) {
                                $card_number = generate_credit_card($bin);
                                $cards[] = [
                                    'Cartão' => $card_number,
                                    'Validade' => generate_random_date($mes, $ano),
                                    'CVV' => generate_random_cvv($bin),
                                    'Nome' => generate_random_name(),
                                    'CPF' => generate_random_cpf(),
                                    'Data de Nascimento' => generate_random_birthdate(),
                                    'Válido' => 'Sim'
                                ];
                            }
                            return $cards;
                        }

                        function generate_credit_card($bin) {
                            $card_number = '';
                            foreach (str_split($bin) as $char) {
                                if ($char == 'x') {
                                    $card_number .= rand(0, 9);
                                } else {
                                    $card_number .= $char;
                                }
                            }

                            // Verificar o primeiro dígito do BIN
                            $length = $bin[0] == '3' ? 15 : 16;

                            // Completar o número do cartão para 15 ou 16 dígitos
                            while (strlen($card_number) < ($length - 1)) {
                                $card_number .= rand(0, 9);
                            }

                            // Adicionar o último dígito para satisfazer o algoritmo de Luhn
                            $card_number .= calculate_luhn_check_digit($card_number);

                            return $card_number;
                        }

                        function calculate_luhn_check_digit($number) {
                            $sum = 0;
                            $alternate = true;

                            for ($i = strlen($number) - 1; $i >= 0; $i--) {
                                $n = intval($number[$i]);
                                if ($alternate) {
                                    $n *= 2;
                                    if ($n > 9) {
                                        $n -= 9;
                                    }
                                }
                                $sum += $n;
                                $alternate = !$alternate;
                            }

                            $check_digit = (10 - ($sum % 10)) % 10;
                            return $check_digit;
                        }

                        function generate_random_date($mes, $ano) {
                            $month = str_pad($mes, 2, '0', STR_PAD_LEFT);
                            $year = substr($ano, -2);
                            return $month . '/' . $year;
                        }

                        function generate_random_cvv($bin) {
                            if ($bin[0] == '3') {
                                return str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
                            } else {
                                return str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
                            }
                        }

                        function generate_random_name() {
                            $first_names = ["Ana", "Carlos", "Maria", "Pedro", "João", "Paula", "Lucas", "Mariana", "Rafael", "Juliana"];
                            $last_names = ["Silva", "Souza", "Costa", "Santos", "Oliveira", "Pereira", "Rodrigues", "Almeida", "Nascimento", "Lima"];
                            return $first_names[array_rand($first_names)] . ' ' . $last_names[array_rand($last_names)];
                        }

                        function generate_random_cpf() {
                            $n1 = rand(0, 9);
                            $n2 = rand(0, 9);
                            $n3 = rand(0, 9);
                            $n4 = rand(0, 9);
                            $n5 = rand(0, 9);
                            $n6 = rand(0, 9);
                            $n7 = rand(0, 9);
                            $n8 = rand(0, 9);
                            $n9 = rand(0, 9);
                            $d1 = $n9 * 2 + $n8 * 3 + $n7 * 4 + $n6 * 5 + $n5 * 6 + $n4 * 7 + $n3 * 8 + $n2 * 9 + $n1 * 10;
                            $d1 = 11 - ($d1 % 11);
                            if ($d1 >= 10) {
                                $d1 = 0;
                            }
                            $d2 = $d1 * 2 + $n9 * 3 + $n8 * 4 + $n7 * 5 + $n6 * 6 + $n5 * 7 + $n4 * 8 + $n3 * 9 + $n2 * 10 + $n1 * 11;
                            $d2 = 11 - ($d2 % 11);
                            if ($d2 >= 10) {
                                $d2 = 0;
                            }
                            return sprintf('%d%d%d.%d%d%d.%d%d%d-%d%d', $n1, $n2, $n3, $n4, $n5, $n6, $n7, $n8, $n9, $d1, $d2);
                        }

                        function generate_random_birthdate() {
                            $year = rand(1950, 2000);
                            $month = rand(1, 12);
                            $day = rand(1, 28); // para simplificação
                            return str_pad($day, 2, '0', STR_PAD_LEFT) . '/' . str_pad($month, 2, '0', STR_PAD_LEFT) . '/' . $year;
                        }

                        function display_data_with_style($cards) {
                            echo '<div class="result">';
                            echo '<textarea id="resultTextArea">';
                            foreach ($cards as $card) {
                                echo 'Cartão: ' . $card['Cartão'] . "\n";
                                echo 'Validade: ' . $card['Validade'] . "\n";
                                echo 'CVV: ' . $card['CVV'] . "\n";
                                echo 'Nome: ' . $card['Nome'] . "\n";
                                echo 'CPF: ' . $card['CPF'] . "\n";
                                echo 'Data de Nascimento: ' . $card['Data de Nascimento'] . "\n";
                                echo 'Válido: ' . $card['Válido'] . "\n";
                                echo "\n";
                            }
                            echo '</textarea>';
                            echo '<button onclick="copyToClipboard()" class="btn btn-success">Copiar</button>';
                            echo '</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script>
        function copyToClipboard() {
            var copyText = document.getElementById("resultTextArea");
            copyText.select();
            document.execCommand("copy");
            Swal.fire({
                icon: 'success',
                title: 'Copiado!',
                text: 'Os dados foram copiados para a área de transferência'
            });
        }
    </script>
</body>
</html>