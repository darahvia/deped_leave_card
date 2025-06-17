            <?php
                $credits_query = "SELECT VL, SL, SPL, FL, solo_parent, ML, PL, RA9710, RL, SEL, study_leave FROM employee_list WHERE id = $employee_id LIMIT 1";
                $credits_result = $conn->query($credits_query);
                if ($credits_result && $credits_result->num_rows > 0) {
                    $credits_row = $credits_result->fetch_assoc();
                    $vl = $credits_row['VL'];
                    $sl = $credits_row['SL'];
                    $spl = $credits_row['SPL'];
                    $fl = $credits_row['FL'];
                    $solo_parent = $credits_row['solo_parent'];
                    $ml = $credits_row['ML'];
                    $pl = $credits_row['PL'];
                    $ra9710 = $credits_row['RA9710'];
                    $rl = $credits_row['RL'];
                    $sel = $credits_row['SEL'];
                    $study_leave = $credits_row['study_leave'];
                }
                
                echo '<table class="leave-table">
                    <tr>
                    <th rowspan="2" style="background:#c6e2e9;">Date Filed</th>
                    <th rowspan="2" style="background:#b5cdfa;">Date Incurred</th>
                    <th colspan="6" style="background:#fdf5d6; text-align:center;">Leave Incurred (Days)</th>
                    <th rowspan="2" style="background:#fdf5d6;">Remarks</th>
                    <th rowspan="2" style="background:#e2f7d6;">Current VL</th>
                    <th rowspan="2" style="background:#e2f7d6;">Current SL</th>

                    <th rowspan="2" style="background:#f4f7fa;"></th>
                    </tr>
                    <tr>
                    <th style="background:#fdf5d6;">VL</th>
                    <th style="background:#fdf5d6;">SL</th>
                    <th style="background:#fdf5d6;">SPL</th>
                    <th style="background:#fdf5d6;">FL</th>
                    <th style="background:#fdf5d6;">Solo Parent</th>
                    <th style="background:#fdf5d6;">Others</th>
                    </tr>
                ';

                if (!empty($leaves)) {
                    foreach ($leaves as $month => $monthLeaves) {
                    foreach ($monthLeaves as $leave) {
                        echo "<tr id='row-{$leave['id']}'>
                        <td data-field='date_filed'>{$leave['date_filed']}</td>
                        <td data-field='date_incurred'>{$leave['date_incurred']}</td>
                        <td>".($vl!==''?$vl:'')."</td>
                        <td>".($sl!==''?$sl:'')."</td>
                        <td>".($spl!==''?$spl:'')."</td>
                        <td>".($fl!==''?$fl:'')."</td>
                        <td>".($solo_parent!==''?$solo_parent:'')."</td>
                        <td></td>
                        <td></td>
                        <td>".(isset($leave['current_vl']) ? $leave['current_vl'] : '')."</td>
                        <td>".(isset($leave['current_sl']) ? $leave['current_sl'] : '')."</td>
                        </tr>";
                    }
                    }
                } else {
                    echo "<tr><td colspan='12' style='text-align:center;'>No leaves filed</td></tr>";
                }

                echo "</table>";
            ?>