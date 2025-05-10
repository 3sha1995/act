<?php
$servicesSection = new ServicesSection();
$servicesData = $servicesSection->getData();
?>

<h2>Services Section</h2>
<form action="" method="post" enctype="multipart/form-data">
    <label for="title">Section Title:</label>
    <input type="text" name="title" id="title" value="<?php echo $servicesData['title']; ?>" required><br><br>

    <label for="card_title">Service Card Title:</label>
    <input type="text" name="card_title" id="card_title" value="<?php echo $servicesData['card_title']; ?>" required><br><br>

    <label for="description">Description:</label>
    <textarea name="description" id="description" required><?php echo $servicesData['description']; ?></textarea><br><br>

    <label for="process_steps">Process Steps:</label>
    <textarea name="process_steps" id="process_steps" required><?php echo $servicesData['process_steps']; ?></textarea><br><br>

    <label for="file">Upload File:</label>
    <input type="file" name="file" id="file"><br><br>

    <button type="submit" name="submit">Update</button>
</form>
