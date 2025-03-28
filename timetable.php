<?php
require_once "./assets/php/config.php";
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timetable | <?= APP_NAME ?></title>

    <link rel="stylesheet" href="<?= CSS_DIR ?>style.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
        .lunch {
            writing-mode: vertical-rl;
            text-orientation: upright;
            font-weight: bold;
        }
        .draggable {
            cursor: grab;
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let draggedElement = null;

            document.querySelectorAll("td[key]").forEach(td => {
                td.draggable = true;

                td.addEventListener("dragstart", function(e) {
                    draggedElement = e.target;
                    e.dataTransfer.setData("text/plain", draggedElement.innerHTML);
                });

                td.addEventListener("dragover", function(e) {
                    e.preventDefault();
                });

                td.addEventListener("drop", function(e) {
                    e.preventDefault();
                    if (draggedElement && draggedElement !== e.target) {
                        let temp = e.target.innerHTML;
                        e.target.innerHTML = draggedElement.innerHTML;
                        draggedElement.innerHTML = temp;
                    }
                });
            });
        });
    </script>
</head>

<body>
    <div id="wrapper">
        <?php include "./inc/header.php" ?>

        <div id="main">
            <?php include "./inc/nav.php" ?>

            <div id="main_section">
                <div class="container">

                    <div class="page-title">
                        <h3>Timetable</h3>
                    </div>
                    
                    <div class="table-record">
                    <table>
                            <tr>
                                <th>Day</th>
                                <th>Semester</th>
                                <th>10:30-11:30</th>
                                <th>11:30-12:30</th>
                                <th>12:30-01:30</th>
                                <th>01:30-02:15</th>
                                <th>02:15-03:15</th>
                                <th>03:15-04:15</th>
                                <th>04:15-05:15</th>
                            </tr>
                            
                            <!-- Monday -->
                            <tr>
                                <td rowspan="4">Monday</td>
                                <td>IV</td>
                                <td key="0" class="draggable">ADA Lab (PP+AKM)</td>
                                <td key="1" class="draggable">DBMS LAB (RS2+NS)</td>
                                <td key="2" class="draggable">OS (VG)</td>
                                <td rowspan="4" class="lunch">LUNCH</td>
                                <td key="3" class="draggable">COA (NS)</td>
                                <td key="4" class="draggable">Python (AM)</td>
                                <td key="5" class="draggable">UHV (AKM)</td>
                            </tr>
                            <tr>
                                <td>VI</td>
                                <td key="6" class="draggable">D. Mining (VG)</td>
                                <td key="7" class="draggable">CD (RP)</td>
                                <td key="8" class="draggable">NWS (YK)</td>
                                <td key="9" class="draggable">Python Lab (PP+AKM+VG)</td>
                                <td key="10" class="draggable">Major Project (All Faculty)</td>
                                <td key="11" class="draggable">IOT (RS1)</td>
                            </tr>
                            <tr>
                                <td>VIII</td>
                                <td key="12" class="draggable">AI (RS1)</td>
                                <td key="13" class="draggable">Robotics (SO)</td>
                                <td key="14" class="draggable">Cloud (CAM)</td>
                                <td colspan="3" key="15" class="draggable">DSA (RP)</td>
                            </tr>
                            <tr>
                                <td>ME/CM</td>
                                <td></td>
                                <td></td>
                                <td key="16" class="draggable">PPS LAB (B1/RS2+RS1)</td>
                                <td key="17" class="draggable">PPS LAB (B1/RS2+RS1)</td>
                                <td></td>
                                <td></td>
                            </tr>
                            
                            <!-- Tuesday -->
                            <tr>
                                <td rowspan="4">Tuesday</td>
                                <td>IV</td>
                                <td key="0" class="draggable">ADA Lab (PP+AKM)</td>
                                <td key="1" class="draggable">DBMS LAB (RS2+NS)</td>
                                <td key="2" class="draggable">OS (VG)</td>
                                <td rowspan="4" class="lunch">LUNCH</td>
                                <td key="3" class="draggable">COA (NS)</td>
                                <td key="4" class="draggable">Python (AM)</td>
                                <td key="5" class="draggable">UHV (AKM)</td>
                            </tr>
                            <tr>
                                <td>VI</td>
                                <td key="6" class="draggable">D. Mining (VG)</td>
                                <td key="7" class="draggable">CD (RP)</td>
                                <td key="8" class="draggable">NWS (YK)</td>
                                <td key="9" class="draggable">Python Lab (PP+AKM+VG)</td>
                                <td key="10" class="draggable">Major Project (All Faculty)</td>
                                <td key="11" class="draggable">IOT (RS1)</td>
                            </tr>
                            <tr>
                                <td>VIII</td>
                                <td key="12" class="draggable">AI (RS1)</td>
                                <td key="13" class="draggable">Robotics (SO)</td>
                                <td key="14" class="draggable">Cloud (CAM)</td>
                                <td colspan="3" key="15" class="draggable">DSA (RP)</td>
                            </tr>
                            <tr>
                                <td>ME/CM</td>
                                <td></td>
                                <td></td>
                                <td key="16" class="draggable">PPS LAB (B1/RS2+RS1)</td>
                                <td key="17" class="draggable">PPS LAB (B1/RS2+RS1)</td>
                                <td></td>
                                <td></td>
                            </tr>

                        <!-- Wednesday -->
                            <tr>
                                <td rowspan="4">Wednesday</td>
                                <td>IV</td>
                                <td key="0" class="draggable">ADA Lab (PP+AKM)</td>
                                <td key="1" class="draggable">DBMS LAB (RS2+NS)</td>
                                <td key="2" class="draggable">OS (VG)</td>
                                <td rowspan="4" class="lunch">LUNCH</td>
                                <td key="3" class="draggable">COA (NS)</td>
                                <td key="4" class="draggable">Python (AM)</td>
                                <td key="5" class="draggable">UHV (AKM)</td>
                            </tr>
                            <tr>
                                <td>VI</td>
                                <td key="6" class="draggable">D. Mining (VG)</td>
                                <td key="7" class="draggable">CD (RP)</td>
                                <td key="8" class="draggable">NWS (YK)</td>
                                <td key="9" class="draggable">Python Lab (PP+AKM+VG)</td>
                                <td key="10" class="draggable">Major Project (All Faculty)</td>
                                <td key="11" class="draggable">IOT (RS1)</td>
                            </tr>
                            <tr>
                                <td>VIII</td>
                                <td key="12" class="draggable">AI (RS1)</td>
                                <td key="13" class="draggable">Robotics (SO)</td>
                                <td key="14" class="draggable">Cloud (CAM)</td>
                                <td colspan="3" key="15" class="draggable">DSA (RP)</td>
                            </tr>
                            <tr>
                                <td>ME/CM</td>
                                <td></td>
                                <td></td>
                                <td key="16" class="draggable">PPS LAB (B1/RS2+RS1)</td>
                                <td key="17" class="draggable">PPS LAB (B1/RS2+RS1)</td>
                                <td></td>
                                <td></td>
                            </tr>

                            <!-- Thursday -->
                            <tr>
                                <td rowspan="4">Thursday</td>
                                <td>IV</td>
                                <td key="0" class="draggable">ADA Lab (PP+AKM)</td>
                                <td key="1" class="draggable">DBMS LAB (RS2+NS)</td>
                                <td key="2" class="draggable">OS (VG)</td>
                                <td rowspan="4" class="lunch">LUNCH</td>
                                <td key="3" class="draggable">COA (NS)</td>
                                <td key="4" class="draggable">Python (AM)</td>
                                <td key="5" class="draggable">UHV (AKM)</td>
                            </tr>
                            <tr>
                                <td>VI</td>
                                <td key="6" class="draggable">D. Mining (VG)</td>
                                <td key="7" class="draggable">CD (RP)</td>
                                <td key="8" class="draggable">NWS (YK)</td>
                                <td key="9" class="draggable">Python Lab (PP+AKM+VG)</td>
                                <td key="10" class="draggable">Major Project (All Faculty)</td>
                                <td key="11" class="draggable">IOT (RS1)</td>
                            </tr>
                            <tr>
                                <td>VIII</td>
                                <td key="12" class="draggable">AI (RS1)</td>
                                <td key="13" class="draggable">Robotics (SO)</td>
                                <td key="14" class="draggable">Cloud (CAM)</td>
                                <td colspan="3" key="15" class="draggable">DSA (RP)</td>
                            </tr>
                            <tr>
                                <td>ME/CM</td>
                                <td></td>
                                <td></td>
                                <td key="16" class="draggable">PPS LAB (B1/RS2+RS1)</td>
                                <td key="17" class="draggable">PPS LAB (B1/RS2+RS1)</td>
                                <td></td>
                                <td></td>
                            </tr>

                        <!-- Friday -->
                            <tr>
                                <td rowspan="4">Friday</td>
                                <td>IV</td>
                                <td key="0" class="draggable">ADA Lab (PP+AKM)</td>
                                <td key="1" class="draggable">DBMS LAB (RS2+NS)</td>
                                <td key="2" class="draggable">OS (VG)</td>
                                <td rowspan="4" class="lunch">LUNCH</td>
                                <td key="3" class="draggable">COA (NS)</td>
                                <td key="4" class="draggable">Python (AM)</td>
                                <td key="5" class="draggable">UHV (AKM)</td>
                            </tr>
                            <tr>
                                <td>VI</td>
                                <td key="6" class="draggable">D. Mining (VG)</td>
                                <td key="7" class="draggable">CD (RP)</td>
                                <td key="8" class="draggable">NWS (YK)</td>
                                <td key="9" class="draggable">Python Lab (PP+AKM+VG)</td>
                                <td key="10" class="draggable">Major Project (All Faculty)</td>
                                <td key="11" class="draggable">IOT (RS1)</td>
                            </tr>
                            <tr>
                                <td>VIII</td>
                                <td key="12" class="draggable">AI (RS1)</td>
                                <td key="13" class="draggable">Robotics (SO)</td>
                                <td key="14" class="draggable">Cloud (CAM)</td>
                                <td colspan="3" key="15" class="draggable">DSA (RP)</td>
                            </tr>
                            <tr>
                                <td>ME/CM</td>
                                <td></td>
                                <td></td>
                                <td key="16" class="draggable">PPS LAB (B1/RS2+RS1)</td>
                                <td key="17" class="draggable">PPS LAB (B1/RS2+RS1)</td>
                                <td></td>
                                <td></td>
                            </tr>

                        <!-- Saturday -->
                            <tr>
                                <td rowspan="4">Saturday</td>
                                <td>IV</td>
                                <td key="0" class="draggable">ADA Lab (PP+AKM)</td>
                                <td key="1" class="draggable">DBMS LAB (RS2+NS)</td>
                                <td key="2" class="draggable">OS (VG)</td>
                                <td rowspan="4" class="lunch">LUNCH</td>
                                <td key="3" class="draggable">COA (NS)</td>
                                <td key="4" class="draggable">Python (AM)</td>
                                <td key="5" class="draggable">UHV (AKM)</td>
                            </tr>
                            <tr>
                                <td>VI</td>
                                <td key="6" class="draggable">D. Mining (VG)</td>
                                <td key="7" class="draggable">CD (RP)</td>
                                <td key="8" class="draggable">NWS (YK)</td>
                                <td key="9" class="draggable">Python Lab (PP+AKM+VG)</td>
                                <td key="10" class="draggable">Major Project (All Faculty)</td>
                                <td key="11" class="draggable">IOT (RS1)</td>
                            </tr>
                            <tr>
                                <td>VIII</td>
                                <td key="12" class="draggable">AI (RS1)</td>
                                <td key="13" class="draggable">Robotics (SO)</td>
                                <td key="14" class="draggable">Cloud (CAM)</td>
                                <td colspan="3" key="15" class="draggable">DSA (RP)</td>
                            </tr>
                            <tr>
                                <td>ME/CM</td>
                                <td></td>
                                <td></td>
                                <td key="16" class="draggable">PPS LAB (B1/RS2+RS1)</td>
                                <td key="17" class="draggable">PPS LAB (B1/RS2+RS1)</td>
                                <td></td>
                                <td></td>
                            </tr>

                        </table>
                    </div>
                    
                </div>

            </div>
        </div>
    </div>

    <script src=" <?= JS_DIR ?>script.js">
    </script>
</body>

</html>