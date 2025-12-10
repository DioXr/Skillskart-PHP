<?php
    require 'core/db_connect.php';
    $page_title = "Homepage";
    $sql = "SELECT id, name, description, image FROM languages";
    $result = $conn->query($sql);
    require 'includes/header.php';
?>

<h1 class="main-heading">Welcome to Skillskart</h1>
<p class="sub-heading">
    Choose a language to start your learning journey! We provide clear, curated roadmaps to guide you from beginner to pro.
</p>
    
<div class="card-grid">
    <?php
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $lang_id = htmlspecialchars($row["id"]);
            $lang_name = htmlspecialchars($row["name"]);
            $lang_desc = htmlspecialchars($row["description"]);
            $lang_image = htmlspecialchars($row["image"]);

            echo "
            <a href='roadmap.php?id={$lang_id}'>
                <div class='card'>
                    <img src='{$lang_image}' alt='{$lang_name}'>
                    <h2>{$lang_name}</h2>
                    <p>{$lang_desc}</p>
                </div>
            </a>";
        }
    } else {
        echo "<p>No languages found.</p>";
    }
    $conn->close();
    ?>
</div>

<?php
    require 'includes/footer.php';
?>