<div>
    <?php echo validation_errors(); ?>
    <?php echo form_open('parser') ?>
        <label for="url">Enter URL:</label>
        <input type="input" name="url" /><br />

        <label for="word">Enter word to search:</label>
        <input type="input" name="word" /><br />

        <input type="submit" name="submit" />
    </form>
</div>