<?php
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'student') {
    header('Location: index.html');
    exit();
}

$student_prn = $_SESSION['user_id'];

// Get student class information
$student = $pdo->prepare("SELECT class, division FROM students WHERE prn_number = ?")->execute([$student_prn])->fetch();

// Sample assignments data (in real app, this would come from database)
$assignments = [
    [
        'id' => 1,
        'subject' => 'Mathematics',
        'title' => 'Calculus Problem Set',
        'description' => 'Solve the following calculus problems from chapter 5. Show all your work and submit in PDF format.',
        'due_date' => '2024-02-15',
        'status' => 'pending',
        'submitted_date' => null,
        'marks' => null,
        'max_marks' => 20
    ],
    [
        'id' => 2,
        'subject' => 'Physics',
        'title' => 'Lab Report - Newton\'s Laws',
        'description' => 'Write a comprehensive lab report based on the Newton\'s Laws experiment conducted in class.',
        'due_date' => '2024-02-10',
        'status' => 'submitted',
        'submitted_date' => '2024-02-08',
        'marks' => 18,
        'max_marks' => 20
    ],
    [
        'id' => 3,
        'subject' => 'Computer Science',
        'title' => 'Web Development Project',
        'description' => 'Create a responsive website using HTML, CSS, and JavaScript. The website should have at least 5 pages.',
        'due_date' => '2024-02-20',
        'status' => 'pending',
        'submitted_date' => null,
        'marks' => null,
        'max_marks' => 50
    ],
    [
        'id' => 4,
        'subject' => 'English',
        'title' => 'Essay - Climate Change',
        'description' => 'Write a 1000-word essay on the impact of climate change and potential solutions.',
        'due_date' => '2024-02-05',
        'status' => 'graded',
        'submitted_date' => '2024-02-03',
        'marks' => 42,
        'max_marks' => 50
    ]
];

// Handle assignment submission (simulated)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_assignment'])) {
    $assignment_id = $_POST['assignment_id'];
    $submission_text = $_POST['submission_text'];
    
    // In real application, you would handle file upload and save to database
    $success = "Assignment submitted successfully!";
    
    // Update the assignment status in our sample data
    foreach ($assignments as &$assignment) {
        if ($assignment['id'] == $assignment_id) {
            $assignment['status'] = 'submitted';
            $assignment['submitted_date'] = date('Y-m-d');
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Assignments - Student</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .assignments-container {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .assignment-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid #667eea;
            transition: transform 0.2s ease;
        }

        .assignment-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .assignment-card.overdue {
            border-left-color: #dc3545;
            background: #f8d7da;
        }

        .assignment-card.submitted {
            border-left-color: #28a745;
            background: #d4edda;
        }

        .assignment-card.graded {
            border-left-color: #ffc107;
            background: #fff3cd;
        }

        .assignment-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .assignment-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin: 0;
            color: #333;
        }

        .assignment-subject {
            background: #667eea;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .assignment-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .meta-item i {
            color: #667eea;
            width: 16px;
        }

        .assignment-description {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            border: 1px solid #e0e0e0;
        }

        .assignment-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-pending { background: #dc3545; color: white; }
        .status-submitted { background: #17a2b8; color: white; }
        .status-graded { background: #28a745; color: white; }

        .marks-display {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 10px;
            width: 600px;
            max-width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        @media (max-width: 768px) {
            .assignment-header {
                flex-direction: column;
                gap: 10px;
            }
            
            .assignment-meta {
                grid-template-columns: 1fr;
            }
            
            .assignment-actions {
                flex-direction: column;
            }
            
            .assignment-actions .btn {
                width: 100%;
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
            <li><a href="student_timetable.php"><i class="fas fa-calendar-alt"></i> Timetable</a></li>
            <li><a href="#" class="active"><i class="fas fa-tasks"></i> Assignments</a></li>
            <li><a href="student_profile.php"><i class="fas fa-user"></i> Profile</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>My Assignments</h1>
            <p>Track and submit your academic assignments</p>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="assignments-container">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3>All Assignments</h3>
                <div>
                    <button class="btn btn-primary" onclick="filterAssignments('all')">All</button>
                    <button class="btn btn-warning" onclick="filterAssignments('pending')">Pending</button>
                    <button class="btn btn-info" onclick="filterAssignments('submitted')">Submitted</button>
                    <button class="btn btn-success" onclick="filterAssignments('graded')">Graded</button>
                </div>
            </div>

            <?php if (empty($assignments)): ?>
                <div class="empty-state">
                    <i class="fas fa-tasks"></i>
                    <h4>No Assignments</h4>
                    <p>You don't have any assignments at the moment.</p>
                </div>
            <?php else: ?>
                <?php foreach ($assignments as $assignment): ?>
                    <?php
                    $isOverdue = $assignment['status'] == 'pending' && strtotime($assignment['due_date']) < time();
                    $cardClass = 'assignment-card';
                    if ($isOverdue) $cardClass .= ' overdue';
                    if ($assignment['status'] == 'submitted') $cardClass .= ' submitted';
                    if ($assignment['status'] == 'graded') $cardClass .= ' graded';
                    ?>
                    
                    <div class="<?php echo $cardClass; ?>" data-status="<?php echo $assignment['status']; ?>">
                        <div class="assignment-header">
                            <div>
                                <h4 class="assignment-title"><?php echo $assignment['title']; ?></h4>
                                <span class="assignment-subject"><?php echo $assignment['subject']; ?></span>
                            </div>
                            <span class="status-badge status-<?php echo $assignment['status']; ?>">
                                <?php echo ucfirst($assignment['status']); ?>
                                <?php if ($isOverdue): ?> (Overdue)<?php endif; ?>
                            </span>
                        </div>

                        <div class="assignment-meta">
                            <div class="meta-item">
                                <i class="fas fa-calendar-alt"></i>
                                <strong>Due Date:</strong> 
                                <?php echo date('M j, Y', strtotime($assignment['due_date'])); ?>
                                <?php if ($isOverdue): ?>
                                    <span style="color: #dc3545; font-weight: bold;">(Overdue)</span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($assignment['submitted_date']): ?>
                                <div class="meta-item">
                                    <i class="fas fa-paper-plane"></i>
                                    <strong>Submitted:</strong> 
                                    <?php echo date('M j, Y', strtotime($assignment['submitted_date'])); ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($assignment['marks'] !== null): ?>
                                <div class="meta-item">
                                    <i class="fas fa-star"></i>
                                    <strong>Marks:</strong> 
                                    <span class="marks-display">
                                        <?php echo $assignment['marks']; ?>/<?php echo $assignment['max_marks']; ?>
                                        (<?php echo round(($assignment['marks'] / $assignment['max_marks']) * 100, 1); ?>%)
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="assignment-description">
                            <strong>Description:</strong><br>
                            <?php echo nl2br(htmlspecialchars($assignment['description'])); ?>
                        </div>

                        <div class="assignment-actions">
                            <?php if ($assignment['status'] == 'pending'): ?>
                                <button class="btn btn-primary" onclick="openSubmissionModal(<?php echo $assignment['id']; ?>)">
                                    <i class="fas fa-upload"></i> Submit Assignment
                                </button>
                            <?php endif; ?>
                            
                            <button class="btn btn-info" onclick="viewAssignmentDetails(<?php echo $assignment['id']; ?>)">
                                <i class="fas fa-eye"></i> View Details
                            </button>
                            
                            <?php if ($assignment['status'] == 'graded'): ?>
                                <button class="btn btn-success" onclick="downloadFeedback(<?php echo $assignment['id']; ?>)">
                                    <i class="fas fa-download"></i> Download Feedback
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="table-container">
            <h3>Assignment Statistics</h3>
            <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <div class="stat-card">
                    <i class="fas fa-tasks"></i>
                    <h3><?php echo count($assignments); ?></h3>
                    <p>Total Assignments</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-clock"></i>
                    <h3><?php echo count(array_filter($assignments, function($a) { 
                        return $a['status'] == 'pending'; 
                    })); ?></h3>
                    <p>Pending</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-paper-plane"></i>
                    <h3><?php echo count(array_filter($assignments, function($a) { 
                        return $a['status'] == 'submitted'; 
                    })); ?></h3>
                    <p>Submitted</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-star"></i>
                    <h3><?php echo count(array_filter($assignments, function($a) { 
                        return $a['status'] == 'graded'; 
                    })); ?></h3>
                    <p>Graded</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Submission Modal -->
    <div id="submissionModal" class="modal">
        <div class="modal-content">
            <h3>Submit Assignment</h3>
            <form method="POST" id="submissionForm">
                <input type="hidden" name="assignment_id" id="assignment_id">
                
                <div class="form-group">
                    <label>Assignment Title</label>
                    <input type="text" id="modal_assignment_title" readonly style="background: #f8f9fa;">
                </div>
                
                <div class="form-group">
                    <label>Submission Text</label>
                    <textarea name="submission_text" rows="6" placeholder="Enter your assignment submission here..." required></textarea>
                </div>
                
                <div class="form-group">
                    <label>Upload Files (Optional)</label>
                    <input type="file" name="submission_files[]" multiple accept=".pdf,.doc,.docx,.txt,.zip">
                    <small>Supported formats: PDF, Word, Text, ZIP (Max: 10MB per file)</small>
                </div>
                
                <div class="form-group">
                    <button type="submit" name="submit_assignment" class="btn btn-success">
                        <i class="fas fa-paper-plane"></i> Submit Assignment
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function filterAssignments(status) {
            const cards = document.querySelectorAll('.assignment-card');
            cards.forEach(card => {
                if (status === 'all' || card.dataset.status === status) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        function openSubmissionModal(assignmentId) {
            // In real implementation, you would fetch assignment details via AJAX
            const assignment = {
                1: { title: 'Calculus Problem Set' },
                2: { title: 'Lab Report - Newton\'s Laws' },
                3: { title: 'Web Development Project' },
                4: { title: 'Essay - Climate Change' }
            };
            
            document.getElementById('assignment_id').value = assignmentId;
            document.getElementById('modal_assignment_title').value = assignment[assignmentId].title;
            document.getElementById('submissionModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('submissionModal').style.display = 'none';
        }

        function viewAssignmentDetails(assignmentId) {
            alert('Viewing details for assignment #' + assignmentId + '\n\nIn a real application, this would show detailed assignment information, rubrics, and submission history.');
        }

        function downloadFeedback(assignmentId) {
            alert('Downloading feedback for assignment #' + assignmentId + '\n\nIn a real application, this would download the graded assignment with teacher comments.');
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('submissionModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>