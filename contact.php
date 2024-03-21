<?php
/**
 * @author Colin Eade
 * @author Megan Clarke
 * @filename contact.php
 */

include 'includes/header.php';
?>

<div class="row">
    <div class="col-md-6 offset-md-3 mt-5 mb-5">
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="card-title">Contact</h2>
                <p>Contact us for general support. We are happy to help!</p>
                <?php
                // Session flash
                if (has_flash_message()) {
                    echo flash_message();
                }
                ?>
                <form action="contact_email_processing.php" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="sender">From:</label>
                        <input type="email" name="sender" required class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="subject">Subject:</label>
                        <input type="text" name="subject" class="form-control">
                    </div>
                    <div class="form-group mb-2">
                        <label for="message">Message:</label>
                        <textarea name="message" rows="5" class="form-control" style="resize: none;"></textarea>
                    </div>
                    <div class="form-group mb-2">
                        <label for="attachment">Attachment:</label>
                        <input type="file" name="attachment" class="form-control-file">
                    </div>
                    <button type="submit" class="btn btn-primary">Send Email</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
include 'includes/footer.php';
?>
