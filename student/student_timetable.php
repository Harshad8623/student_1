<?php
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'student') {
    header('Location: index.html');
    exit();
}

$student_prn = $_SESSION['user_id'];

// Get student class information
$student = $pdo->prepare("SELECT class, division FROM students WHERE prn_number = ?")->execute([$student_prn])->fetch();

// Sample timetable data (in real app, this would come from database)
$timetable = [
    'Monday' => [
        ['time' => '09:00 - 10:00', 'subject' => 'Mathematics', 'teacher' => 'Dr. Smith', 'room' => 'Room 101'],
        ['time' => '10:00 - 11:00', 'subject' => 'Physics', 'teacher' => 'Prof. Johnson', 'room' => 'Lab 201'],
        ['time' => '11:15 - 12:15', 'subject' => 'Chemistry', 'teacher' => 'Dr. Brown', 'room' => 'Room 102'],
        ['time' => '13:30 - 14:30', 'subject' => 'English', 'teacher' => 'Ms. Davis', 'room' => 'Room 103'],
    ],
    'Tuesday' => [
        ['time' => '09:00 - 10:00', 'subject' => 'Computer Science', 'teacher' => 'Mr. Wilson', 'room' => 'Lab 301'],
        ['time' => '10:00 - 11:00', 'subject' => 'Mathematics', 'teacher' => 'Dr. Smith', 'room' => 'Room 101'],
        ['time' => '11:15 - 12:15', 'subject' => 'Physics Lab', 'teacher' => 'Prof. Johnson', 'room' => 'Lab 201'],
    ],
    'Wednesday' => [
        ['time' => '09:00 - 10:00', 'subject' => 'Chemistry Lab', 'teacher' => 'Dr. Brown', 'room' => 'Lab 202'],
        ['time' => '10:00 - 11:00', 'subject' => 'English', 'teacher' => 'Ms. Davis', 'room' => 'Room 103'],
        ['time' => '11:15 - 12:15', 'subject' => 'Mathematics', 'teacher' => 'Dr. Smith', 'room' => 'Room 101'],
    ],
    'Thursday' => [
        ['time' => '09:00 - 10:00', 'subject' => 'Physics', 'teacher' => 'Prof. Johnson', 'room' => 'Room 101'],
        ['time' => '10:00 - 11:00', 'subject' => 'Computer Science', 'teacher' => 'Mr. Wilson', 'room' => 'Lab 301'],
        ['time' => '11:15 - 12:15', 'subject' => 'Library', 'teacher' => 'Library Staff', 'room' => 'Library'],
    ],
    'Friday' => [
        ['time' => '09:00 - 10:00', 'subject' => 'Chemistry', 'teacher' => 'Dr. Brown', 'room' => 'Room 102'],
        ['time' => '10:00 - 11:00', 'subject' => 'Mathematics', 'teacher' => 'Dr. Smith', 'room' => 'Room 101'],
        ['time' => '11:15 - 12:15', 'subject' => 'Sports', 'teacher' => 'Coach Miller', 'room' => 'Sports Ground'],
    ]
];

// Get current day and next class
$currentDay = date('l');
$currentTime = date('H:i');
$nextClass = null;

foreach ($timetable[$currentDay] ?? [] as $class) {
    $classTime = explode(' - ', $class['time'])[0];
    if ($classTime > $currentTime) {
        $nextClass = $class;
        break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Timetable - Student</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .timetable-container {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .timetable-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .day-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            border-left: 4px solid #667eea;
        }

        .day-card.current-day {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .day-card h3 {
            margin: 0 0 15px 0;
            font-size: 1.2rem;
        }

        .class-slot {
            background: white;
            padding: 12px;
            margin-bottom: 10px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .class-slot.current {
            border: 2px solid #28a745;
            background: #d4edda;
        }

        .class-slot.next {
            border: 2px solid #ffc107;
            background: #fff3cd;
        }

        .class-time {
            font-weight: bold;
            color: #667eea;
            margin-bottom: 5px;
        }

        .class-subject {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 3px;
        }

        .class-details {
            font-size: 0.9rem;
            color: #666;
        }

        .day-card.current-day .class-details {
            color: rgba(255,255,255,0.8);
        }

        .next-class-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .no-classes {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        .no-classes i {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        @media (max-width: 768px) {
            .timetable-grid {
                grid-template-columns: 1fr;
            }
            
            .day-card {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <h2><i class="fas fa-graduation-cap"></i> SMS</h2>
        </div>
        <ul class="nav-links">
            <li><a href="student_dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="student_attendance.php"><i class="fas fa-calendar-check"></i> Attendance</a></li>
            <li><a href="student_marks.php"><i class="fas fa-chart-bar"></i> Marks & Grades</a></li>
            <li><a href="#" class="active"><i class="fas fa-calendar-alt"></i> Timetable</a></li>
            <li><a href="student_notices.php"><i class="fas fa-bullhorn"></i> Notices</a></li>
            <li><a href="student_profile.php"><i class="fas fa-user"></i> Profile</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>My Class Timetable</h1>
            <p>Class: <?php echo $student['class'] . ' ' . $student['division']; ?> | Current Day: <?php echo $currentDay; ?></p>
        </div>

        <?php if ($nextClass): ?>
        <div class="next-class-info">
            <h3><i class="fas fa-clock"></i> Next Class Today</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 15px;">
                <div>
                    <strong>Subject:</strong><br>
                    <?php echo $nextClass['subject']; ?>
                </div>
                <div>
                    <strong>Time:</strong><br>
                    <?php echo $nextClass['time']; ?>
                </div>
                <div>
                    <strong>Teacher:</strong><br>
                    <?php echo $nextClass['teacher']; ?>
                </div>
                <div>
                    <strong>Room:</strong><br>
                    <?php echo $nextClass['room']; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="timetable-container">
            <h3>Weekly Schedule</h3>
            
            <?php if (empty($timetable)): ?>
                <div class="no-classes">
                    <i class="fas fa-calendar-times"></i>
                    <h4>No Timetable Available</h4>
                    <p>Your class timetable has not been published yet.</p>
                </div>
            <?php else: ?>
                <div class="timetable-grid">
                    <?php foreach ($timetable as $day => $classes): ?>
                        <div class="day-card <?php echo $day == $currentDay ? 'current-day' : ''; ?>">
                            <h3>
                                <?php echo $day; ?>
                                <?php if ($day == $currentDay): ?>
                                    <small>(Today)</small>
                                <?php endif; ?>
                            </h3>
                            
                            <?php if (empty($classes)): ?>
                                <div class="class-slot">
                                    <div class="class-time">No classes</div>
                                    <div class="class-details">Holiday / No Schedule</div>
                                </div>
                            <?php else: ?>
                                <?php foreach ($classes as $class): ?>
                                    <?php
                                    $isCurrent = false;
                                    $isNext = false;
                                    
                                    if ($day == $currentDay) {
                                        $classTimes = explode(' - ', $class['time']);
                                        $startTime = $classTimes[0];
                                        $endTime = $classTimes[1];
                                        
                                        if ($currentTime >= $startTime && $currentTime <= $endTime) {
                                            $isCurrent = true;
                                        } elseif ($class === $nextClass) {
                                            $isNext = true;
                                        }
                                    }
                                    ?>
                                    <div class="class-slot <?php echo $isCurrent ? 'current' : ''; ?> <?php echo $isNext ? 'next' : ''; ?>">
                                        <div class="class-time">
                                            <?php echo $class['time']; ?>
                                            <?php if ($isCurrent): ?>
                                                <span style="color: #28a745; font-size: 0.8rem;">(Now)</span>
                                            <?php elseif ($isNext): ?>
                                                <span style="color: #ffc107; font-size: 0.8rem;">(Next)</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="class-subject"><?php echo $class['subject']; ?></div>
                                        <div class="class-details">
                                            <i class="fas fa-chalkboard-teacher"></i> <?php echo $class['teacher']; ?><br>
                                            <i class="fas fa-door-open"></i> <?php echo $class['room']; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="table-container">
            <h3>Quick Actions</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <button class="btn btn-primary" onclick="window.print()">
                    <i class="fas fa-print"></i> Print Timetable
                </button>
                <button class="btn btn-success" onclick="downloadTimetable()">
                    <i class="fas fa-download"></i> Download as PDF
                </button>
                <button class="btn btn-info" onclick="shareTimetable()">
                    <i class="fas fa-share-alt"></i> Share Timetable
                </button>
            </div>
        </div>
    </div>

    <script>
        function downloadTimetable() {
            alert('Timetable download feature would be implemented here!');
            // In real implementation, this would generate a PDF
        }

        function shareTimetable() {
            if (navigator.share) {
                navigator.share({
                    title: 'My Class Timetable',
                    text: 'Check out my class schedule',
                    url: window.location.href
                });
            } else {
                alert('Share functionality not supported in your browser');
            }
        }

        // Auto-refresh every minute to update current class status
        setInterval(() => {
            const currentClass = document.querySelector('.class-slot.current');
            if (currentClass) {
                location.reload();
            }
        }, 60000);
    </script>
</body>
</html>