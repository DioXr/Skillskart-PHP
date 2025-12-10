<?php
session_start();
require 'core/db_connect.php';

// Protect this page: user must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get topic ID from URL
$topic_id = isset($_GET['topic_id']) ? (int)$_GET['topic_id'] : 0;
if ($topic_id <= 0) {
    die("Invalid topic specified.");
}

// --- Quiz Logic ---

// If this is the first time the user is starting the quiz, fetch all questions
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    $sql = "SELECT q.id, q.question, GROUP_CONCAT(qo.option_text ORDER BY qo.id) as options, 
                   (SELECT option_text FROM quiz_options WHERE quiz_id = q.id AND is_correct = 1) as correctAnswer
            FROM quizzes q
            JOIN quiz_options qo ON q.id = qo.quiz_id
            WHERE q.topic_id = ?
            GROUP BY q.id";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $topic_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $quiz_questions = [];
    while ($row = $result->fetch_assoc()) {
        $row['options'] = explode(',', $row['options']);
        $quiz_questions[] = $row;
    }
    
    // Store quiz data in the session
    $_SESSION['quiz_data'] = $quiz_questions;
    $_SESSION['current_question_index'] = 0;
    $_SESSION['quiz_score'] = 0;
    $stmt->close();
}

// If the user submitted an answer
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_index = $_SESSION['current_question_index'];
    $correct_answer = $_SESSION['quiz_data'][$current_index]['correctAnswer'];
    
    // Check if the submitted answer is correct
    if (isset($_POST['answer']) && $_POST['answer'] == $correct_answer) {
        $_SESSION['quiz_score']++;
    }
    
    // Move to the next question
    $_SESSION['current_question_index']++;
}

$current_index = $_SESSION['current_question_index'];
$quiz_data = $_SESSION['quiz_data'];
$total_questions = count($quiz_data);

// Check if the quiz is finished
$is_finished = ($current_index >= $total_questions);

require 'includes/header.php';
?>

<h2>Quiz Time!</h2>

<?php if (!$is_finished): 
    $current_question = $quiz_data[$current_index];
?>
    <p>Question <?php echo $current_index + 1; ?> of <?php echo $total_questions; ?></p>
    <h3><?php echo htmlspecialchars($current_question['question']); ?></h3>

    <form action="quiz.php?topic_id=<?php echo $topic_id; ?>" method="post">
        <?php foreach ($current_question['options'] as $option): ?>
            <div class="form-group">
                <label>
                    <input type="radio" name="answer" value="<?php echo htmlspecialchars($option); ?>" required>
                    <?php echo htmlspecialchars($option); ?>
                </label>
            </div>
        <?php endforeach; ?>
        <br>
        <button type="submit">Submit Answer</button>
    </form>

<?php else: ?>
    <h3>Quiz Complete!</h3>
    <p>Your final score is: <?php echo $_SESSION['quiz_score']; ?> out of <?php echo $total_questions; ?></p>
    <a href="topic.php?slug=<?php /* We need the slug here, for now, link back to profile */ echo "profile.php"; ?>">Back to your profile</a>
    <?php
        // Clean up session variables after quiz is done
        unset($_SESSION['quiz_data']);
        unset($_SESSION['current_question_index']);
        unset($_SESSION['quiz_score']);
    ?>
<?php endif; ?>

<?php
$conn->close();
require 'includes/footer.php';
?>