<?php
$maxHours = 24; // max hours in a day

// P = V x I
function getPower($v, $i) {
    return $v * $i;
}

// convert watts over time into kWh
function getEnergy($watts, $hours) {
    return ($watts * $hours) / 1000;
}

// cost = energy used x price per kWh
function getCost($kwh, $rate) {
    return $kwh * ($rate / 100);
}

$voltage = "";
$current = "";
$rate = "";
$power = 0;

$hourArr = array();
$energyArr = array();
$totalChargeArr = array();

$showResults = false;
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $voltage = $_POST["voltage"];
    $current = $_POST["current"];
    $rate = $_POST["rate"];

    if (!is_numeric($voltage) || !is_numeric($current) || !is_numeric($rate)) {
        $error = "Please enter valid numbers for voltage, current and rate.";
    } else if ($voltage <= 0 || $current <= 0 || $rate <= 0) {
        $error = "Voltage, current and rate must be greater than 0.";
    } else {
        $power = getPower($voltage, $current);

        // Fill arrays
        for ($i = 0; $i < $maxHours; $i++) {
            $hour = $i + 1;
            $hourArr[$i] = $hour;
            $energyArr[$i] = getEnergy($power, $hour);
            $totalChargeArr[$i] = getCost($energyArr[$i], $rate);
        }

        $showResults = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Electricity Bill Calculator</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body { background-color: #f4f6f9; }
        .card-header { background-color: #17a2b8; color: #fff; }
        .summary-box { background: #fff; border-radius: .5rem; padding: 1rem; text-align: center; height: 100%; }
        .summary-box h6 { color: #6c757d; text-transform: uppercase; font-size: .8rem; margin-bottom: .5rem; }
        .summary-box .value { font-size: 1.5rem; font-weight: 700; color: #17a2b8; }
        .help-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background-color: #6c757d;
            color: #fff;
            font-size: .7rem;
            font-weight: 700;
            cursor: help;
            vertical-align: middle;
            text-decoration: none;
        }
        .help-icon:hover { background-color: #17a2b8; color: #fff; }
    </style>
</head>
<body>
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h4 class="mb-0">Electricity Calculator</h4>
                </div>
                <div class="card-body">

                    <?php if ($error) { ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php } ?>

                    <form method="POST" action="" id="calcForm">
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Voltage (V)</label>
                                <input type="number" name="voltage" class="form-control" min="0.01" step="any"
                                       value="<?php echo htmlspecialchars($voltage); ?>" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Current (A)</label>
                                <input type="number" name="current" class="form-control" min="0.01" step="any"
                                       value="<?php echo htmlspecialchars($current); ?>" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Current Rate (sen/kWh)
                                    <a href="https://www.mytnb.com.my/tariff/index.html?lang=bm&v=1.1.65#schedule"
                                       target="_blank" rel="noopener noreferrer" class="help-icon"
                                       title="The price your electricity provider charges per kilowatt-hour (kWh), in sen. Click to check TNB's tariff schedule.">?</a>
                                </label>
                                <input type="number" name="rate" class="form-control" min="0.01" step="any"
                                       value="<?php echo htmlspecialchars($rate); ?>" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-md-8">
                                <button type="submit" class="btn btn-info btn-block">Calculate</button>
                            </div>
                            <div class="col-md-4">
                                <button type="button" class="btn btn-secondary btn-block" onclick="window.location.href = window.location.pathname;">Reset</button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>

            <?php if ($showResults) { ?>

                <div class="row mb-4">
                    <div class="col-6 col-md-4 mb-3">
                        <div class="summary-box shadow-sm">
                            <h6>Power</h6>
                            <div class="value"><?php echo number_format($power / 1000.0, 5); ?></div>
                            <small class="text-muted">Kilowatt (kW)</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 mb-3">
                        <div class="summary-box shadow-sm">
                            <h6>Charge / Hour</h6>
                            <div class="value">RM <?php echo number_format($totalChargeArr[0], 4); ?></div>
                            <small class="text-muted">at <?php echo htmlspecialchars($rate); ?> sen/kWh</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 mb-3">
                        <div class="summary-box shadow-sm">
                            <h6>Charge / Day</h6>
                            <div class="value">RM <?php echo number_format($totalChargeArr[$maxHours - 1], 2); ?></div>
                            <small class="text-muted"><?php echo $maxHours; ?> hours</small>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">24-Hour Breakdown</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Hour</th>
                                        <th>Energy (kWh)</th>
                                        <th>Total Charge (RM)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php for ($i = 0; $i < $maxHours; $i++) { ?>
                                    <tr>
                                        <td><?php echo $hourArr[$i]; ?></td>
                                        <td><?php echo number_format($energyArr[$i], 5); ?></td>
                                        <td><?php echo number_format($totalChargeArr[$i], 2); ?></td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            <?php } ?>

        </div>
    </div>
</div>
</body>
</html>
