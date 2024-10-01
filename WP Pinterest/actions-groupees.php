<?php 

if (!defined('ABSPATH')) {
    exit;
}

/* fonction complète pour afficher la pop up et effectuer les requêtes */
add_action('admin_footer', 'enqueue_pinterest_script');

function enqueue_pinterest_script() {
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('body').append('<div id="pinterest-responses" style="position:fixed; bottom:10px; right:10px; width:300px; max-height:400px; overflow:auto; background-color:white; border:1px solid #ccc; box-shadow:0 0 10px rgba(0,0,0,0.2); padding:10px; display:none; z-index:10000;"></div>');
        
            $('body').append('<div id="ajax-loader" style="display:none; position:fixed; top:50%; left:50%; background-color:rgba(255,255,255,0.8); padding:20px; border-radius:10px; z-index:10000;"><img src="<?php echo admin_url('images/spinner.gif'); ?>" alt="Chargement..."></div>');

            $('body').append('<button id="select-board" style="margin: 10px;">Sélectionner un board Pinterest</button>');
            
            $('body').append(`

<style>

#board-selection-modal button{
      border: none;
    background: none;
    padding: 5px;
    font-size: 14px;
    FONT-WEIGHT: 500;
}

#board-selection-modal button:hover{
cursor:pointer;
}



#confirm-board-selection{color: #21c061;}
#export-csv{color: #384944;}
#refresh-boards{color:  #245785;}
#close-board-modal{    
olor: #dd2626;
    position: absolute;
    top: 0;
    right: 0;
}


</style>
               <div id="board-selection-modal" style="display:none; background-color: white; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 10000; border: 1px solid #ccc; padding: 20px; max-height: 400px; overflow: auto;">
            <img src="https://kevin-benabdelhak.fr/wp-content/uploads/2024/10/Logo-Pinterest.png" width="120">
                    <h2>Sélectionnez un tableau</h2>
                    <select id="boards-list" style="width: 100%; margin-bottom: 10px;"></select>
                    <button id="refresh-boards">🔄 Rafraîchir</button>
                    <button id="close-board-modal">❌ Fermer</button>
                    <button id="export-csv" style="margin-top: 10px;">💾 Exporter</button>
                    <button id="confirm-board-selection">📢 Publier</button>
                </div>
            `);

            // Fonction pour récupérer les tableaux et les mettre dans la liste d'options
            function fetchBoards() {
                $.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'get_pinterest_boards',
                        nonce: '<?php echo wp_create_nonce('pinterest_board_nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#boards-list').empty(); 
                            response.data.items.forEach(function(board) {
                                $('#boards-list').append('<option value="' + board.id + '">' + board.name + '</option>');
                            });
                        } else {
                            alert('Erreur: ' + response.data);
                        }
                    }
                });
            }

            $('#select-board').click(function() {
                fetchBoards();
                $('#board-selection-modal').show();
            });

            $('#refresh-boards').click(function() {
                fetchBoards(); 
            });

            $('#close-board-modal').click(function() {
                $('#board-selection-modal').hide();
            });

            $('#confirm-board-selection').click(function() {
                var selectedBoard = $('#boards-list').val();
                if (!selectedBoard) {
                    alert('Veuillez sélectionner un board.');
                } else {
                    $('#board-selection-modal').hide();
                    handleApplyClick(selectedBoard); 
                }
            });

            // Gérer le clic sur le bouton "Exporter"
            $('#export-csv').click(function() {
                var selectedBoard = $('#boards-list').val();
                if (!selectedBoard) {
                    alert('Veuillez sélectionner un board.');
                    return;
                }

                var selectedMedia = [];
                $('tbody th.check-column input[type="checkbox"]:checked').each(function() {
                    selectedMedia.push($(this).val());
                });

                if (selectedMedia.length === 0) {
                    alert('Aucun média sélectionné.');
                    return;
                }

                // Préparation des données pour le CSV
              var csvData = "Title,Media URL,Pinterest board,Thumbnail,Description,Link,Publish date,Keywords\n"; // En-têtes du CSV

                // Récupérer les détails de chaque média sélectionné
                selectedMedia.forEach(function(media_id) {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'get_media_details',
                            media_id: media_id,
                            nonce: '<?php echo wp_create_nonce('get_media_details_nonce'); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                var link = response.data.link ? response.data.link : response.data.media_url;

                              
                                csvData += `${response.data.title},${response.data.media_url},${$('#boards-list option:selected').text()},"","${response.data.description}",${link},"","test"\n`;

                              
                                console.log("CSV Data:", csvData); // Pour déboguer
                                
                           
                                if (media_id === selectedMedia[selectedMedia.length - 1]) {
                                    downloadCSV(csvData);
                                }
                            } else {
                                console.error('Erreur lors de la récupération des détails du média: ' + response.data);
                            }
                        },
                        error: function() {
                            console.error('Erreur AJAX pour le média ID: ' + media_id);
                        }
                    });
                });
            });

            function downloadCSV(csvData) {
                var blob = new Blob([csvData], { type: 'text/csv;charset=utf-8;' });
                var url = URL.createObjectURL(blob);
                var a = document.createElement('a');
                a.href = url;
                a.download = 'export_pinterest.csv';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
            }

            // Gérer le clic sur le bouton "Appliquer"
            function handleApplyClick(boardId) {
                var selectedMedia = [];
                $('tbody th.check-column input[type="checkbox"]:checked').each(function() {
                    selectedMedia.push($(this).val());
                });

                if (selectedMedia.length === 0) {
                    alert('Aucun média sélectionné.');
                    return;
                }

                $('#ajax-loader').show();
                $('#pinterest-responses').show().html('');

                function processNextMedia() {
                    if (selectedMedia.length === 0) {
                        $('#ajax-loader').hide();
                        return;
                    }

                    var media_id = selectedMedia.shift();

                    $.ajax({
                        url: ajaxurl,
                        method: 'POST',
                        data: {
                            action: 'save_to_pinterest',
                            nonce: '<?php echo wp_create_nonce('pinterest_bulk_action_nonce'); ?>',
                            media_id: media_id,
                            board_id: boardId 
                        },
                        success: function(response) {
                            if (response.success) {
                                $('#pinterest-responses').append('<p style="color: green;">Succès: ' + response.data + '</p>');
                            } else {
                                $('#pinterest-responses').append('<p style="color: red;">Erreur: ' + response.data + '</p>');
                                console.error('Erreur lors de la sauvegarde sur Pinterest:', response.data);
                            }
                            processNextMedia(); 
                        },
                        error: function() {
                            $('#pinterest-responses').append('<p style="color: red;">Erreur: Une erreur est survenue.</p>');
                            processNextMedia(); 
                        }
                    });
                }

                processNextMedia(); 
            }

            $('#doaction').click(function(e) {
                if ($('select[name="action"]').val() === 'save_to_pinterest') {
                    e.preventDefault();
                    fetchBoards();
                    $('#board-selection-modal').show(); 
                }
            });

            $('#doaction2').click(function(e) {
                if ($('select[name="action2"]').val() === 'save_to_pinterest') {
                    e.preventDefault();
                    fetchBoards();
                    $('#board-selection-modal').show(); 
                }
            });
        });
    </script>
    <?php
}