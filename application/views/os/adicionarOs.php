<link rel="stylesheet" href="<?php echo base_url(); ?>assets/js/jquery-ui/css/smoothness/jquery-ui-1.9.2.custom.css" />
<script type="text/javascript" src="<?php echo base_url() ?>assets/js/jquery-ui/js/jquery-ui-1.9.2.custom.js"></script>
<script type="text/javascript" src="<?php echo base_url() ?>assets/js/jquery.validate.js"></script>

<link rel="stylesheet" href="<?php echo base_url() ?>assets/trumbowyg/ui/trumbowyg.css">
<script type="text/javascript" src="<?php echo base_url() ?>assets/trumbowyg/trumbowyg.js"></script>
<script type="text/javascript" src="<?php echo base_url() ?>assets/trumbowyg/langs/pt_br.js"></script>

<link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/custom.css" />

<style>
/* ===============================
   ESTILO DO PADRÃO GRÁFICO
   =============================== */
#patternContainer {
  display: flex;
  justify-content: center;
  align-items: center;
  flex-direction: column;
}

#patternGrid {
  display: grid;
  grid-template-columns: repeat(3, 70px);
  grid-template-rows: repeat(3, 70px);
  gap: 30px;
  position: relative;
}

.dot {
  width: 28px;
  height: 28px;
  border: 2px solid #007bff;
  border-radius: 50%;
  background-color: white;
  transition: background-color 0.2s;
}

.dot.active {
  background-color: #007bff;
}

#patternCanvas {
  position: absolute;
  top: 0;
  left: 0;
  pointer-events: none;
}

#resetPattern {
  margin-top: 15px;
  background-color: #dc3545;
  color: #fff;
  border: none;
  padding: 6px 14px;
  border-radius: 5px;
  cursor: pointer;
  font-size: 13px;
  transition: background-color 0.3s;
}
#resetPattern:hover {
  background-color: #c82333;
}
</style>

<div class="row-fluid" style="margin-top:0">
  <div class="span12">
    <div class="widget-box">
      <div class="widget-title">
        <h5>Cadastro de OS</h5>
      </div>
      <div class="widget-content nopadding tab-content">
        <div class="span12" id="divProdutosServicos" style="margin-left:0">
          <ul class="nav nav-tabs">
            <li class="active" id="tabDetalhes"><a href="#tab1" data-toggle="tab">Detalhes da OS</a></li>
          </ul>

          <div class="tab-content">
            <div class="tab-pane active" id="tab1">
              <div class="span12" id="divCadastrarOs">
                <?php if ($custom_error == true) { ?>
                <div class="span12 alert alert-danger" id="divInfo" style="padding:1%;">
                  Dados incompletos, verifique os campos com asterisco ou se selecionou corretamente cliente, responsável e garantia.<br/>
                  Ou se tem um cliente e um termo de garantia cadastrado.
                </div>
                <?php } ?>

                <form action="<?php echo current_url(); ?>" method="post" id="formOs">
                  <div class="span12" style="padding:1%">
                    <div class="span6">
                      <label for="cliente">Cliente<span class="required">*</span></label>
                      <input id="cliente" class="span12" type="text" name="cliente" autocomplete="off"/>
                      <input id="clientes_id" type="hidden" name="clientes_id"/>
                    </div>
                    <div class="span6">
                      <label for="tecnico">Técnico / Responsável<span class="required">*</span></label>
                      <input id="tecnico" class="span12" type="text" name="tecnico"
                             value="<?= $this->session->userdata('nome_admin'); ?>" autocomplete="off"/>
                      <input id="usuarios_id" type="hidden" name="usuarios_id"
                             value="<?= $this->session->userdata('id_admin'); ?>"/>
                    </div>
                  </div>

                  <div class="span12" style="padding:1%; margin-left:0">
                    <div class="span3">
                      <label for="status">Status<span class="required">*</span></label>
                      <select class="span12" name="status" id="status">
                        <option value="Aberto">Aberto</option>
                        <option value="Orçamento">Orçamento</option>
                        <option value="Negociação">Negociação</option>
                        <option value="Aprovado">Aprovado</option>
                        <option value="Aguardando Peças">Aguardando Peças</option>
                        <option value="Em Andamento">Em Andamento</option>
                        <option value="Finalizado">Finalizado</option>
                        <option value="Faturado">Faturado</option>
                        <option value="Cancelado">Cancelado</option>
                      </select>
                    </div>

                    <div class="span3">
                      <label for="dataInicial">Data Inicial<span class="required">*</span></label>
                      <input id="dataInicial" autocomplete="off" class="span12 datepicker" type="text"
                             name="dataInicial" value="<?php echo date('d/m/Y'); ?>"/>
                    </div>

                    <div class="span3">
                      <label for="dataFinal">Data Final<span class="required">*</span></label>
                      <input id="dataFinal" autocomplete="off" class="span12 datepicker" type="text" name="dataFinal"/>
                    </div>

                    <div class="span3">
                      <label for="garantia">Garantia (dias)</label>
                      <input id="garantia" type="number" placeholder="Status s/g inserir nº/0" min="0" max="9999"
                             class="span12" name="garantia"/>
                      <?php echo form_error('garantia'); ?>
                      <label for="termoGarantia">Termo Garantia</label>
                      <input id="termoGarantia" class="span12" type="text" name="termoGarantia"/>
                      <input id="garantias_id" type="hidden" name="garantias_id"/>
                    </div>
                  </div>

                  <div class="span6" style="padding:1%; margin-left:0">
                    <label><h4>Descrição Produto/Serviço</h4></label>
                    <textarea class="span12 editor" name="descricaoProduto" cols="30" rows="5"></textarea>
                  </div>

                  <div class="span6" style="padding:1%; margin-left:0">
                    <label><h4>Defeito</h4></label>
                    <textarea class="span12 editor" name="defeito" cols="30" rows="5"></textarea>
                  </div>

                  <div class="span6" style="padding:1%; margin-left:0">
                    <label><h4>Observações</h4></label>
                    <textarea class="span12 editor" name="observacoes" cols="30" rows="5"></textarea>
                  </div>

                  <div class="span6" style="padding:1%; margin-left:0">
                    <label><h4>Laudo Técnico</h4></label>
                    <textarea class="span12 editor" name="laudoTecnico" cols="30" rows="5"></textarea>
                  </div>

                  <!-- NOVOS CAMPOS -->
                  <div class="span6" style="padding:1%; margin-left:0">
                    <label><h4>Checklist do Aparelho</h4></label>
                    <textarea class="span12 editor" name="checklist" cols="30" rows="5"
                              placeholder="Ex: Tampa trincada, com película, sem chip, com capa, aparelho liga normal..."></textarea>
                  </div>

                  <div class="span6" style="padding:1%; margin-left:0; text-align:center;">
                    <label><h4>Senha de Desbloqueio (Padrão Gráfico)</h4></label>
                    <div id="patternContainer" style="margin-top:10px;">
                      <div id="patternGrid">
                        <canvas id="patternCanvas" width="300" height="300"></canvas>
                        <?php for ($i=1; $i<=9; $i++) echo "<div class='dot' data-id='$i'></div>"; ?>
                      </div>
                      <input type="hidden" name="senha_celular" id="senha_celular">
                      <p style="margin-top:10px; font-size:12px; color:#555;">Desenhe o padrão de desbloqueio (9 pontos)</p>
                      <button id="resetPattern" type="button">Redefinir Padrão</button>
                    </div>
                  </div>
                  <!-- FIM NOVOS CAMPOS -->

                  <div class="span12" style="padding:1%; margin-left:0">
                    <div class="span6 offset3" style="display:flex">
                      <button class="button btn btn-success" id="btnContinuar">
                        <span class="button__icon"><i class='bx bx-chevrons-right'></i></span>
                        <span class="button__text2">Continuar</span>
                      </button>
                      <a href="<?php echo base_url() ?>index.php/os" class="button btn btn-mini btn-warning" style="max-width:160px">
                        <span class="button__icon"><i class="bx bx-undo"></i></span>
                        <span class="button__text2">Voltar</span>
                      </a>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
$(function() {

  // === AUTOCOMPLETE CLIENTE ===
  $("#cliente").autocomplete({
    source: "<?php echo base_url(); ?>index.php/os/autoCompleteCliente",
    minLength: 1,
    select: function(event, ui) {
      $("#clientes_id").val(ui.item.id);
      $("#cliente").val(ui.item.nome);
      return false;
    }
  }).data("ui-autocomplete")._renderItem = function(ul, item) {
    return $("<li></li>")
      .append("<div><strong>" + item.nome + "</strong> | Telefone: " + (item.telefone || '') +
              " | Celular: " + (item.celular || '') +
              " | Documento: " + (item.documento || '') + "</div>")
      .appendTo(ul);
  };

  // === AUTOCOMPLETE TÉCNICO ===
  $("#tecnico").autocomplete({
    source: "<?php echo base_url(); ?>index.php/os/autoCompleteUsuario",
    minLength: 1,
    select: function(event, ui) {
      $("#usuarios_id").val(ui.item.id);
      $("#tecnico").val(ui.item.label);
      return false;
    }
  });

  // === AUTOCOMPLETE TERMO GARANTIA ===
  $("#termoGarantia").autocomplete({
    source: "<?php echo base_url(); ?>index.php/os/autoCompleteTermoGarantia",
    minLength: 1,
    select: function(event, ui) {
      $("#garantias_id").val(ui.item.id);
      $("#termoGarantia").val(ui.item.label);
      return false;
    }
  });

  // === DATAPICKER E TRUMBOWYG ===
  $(".datepicker").datepicker({ dateFormat: "dd/mm/yy" });
  $(".editor").trumbowyg({ lang: "pt_br", semantic: { strikethrough: "s" } });

  // === PADRÃO GRÁFICO ===
  const grid = document.getElementById('patternGrid');
  const canvas = document.getElementById('patternCanvas');
  const ctx = canvas.getContext('2d');
  const dots = [...document.querySelectorAll('.dot')];
  const senhaInput = document.getElementById('senha_celular');
  const resetBtn = document.getElementById('resetPattern');

  let isDrawing = false;
  let pattern = [];
  let lastDot = null;

  function getDotCenter(dot) {
    const rect = dot.getBoundingClientRect();
    const gridRect = grid.getBoundingClientRect();
    return {
      x: rect.left - gridRect.left + rect.width / 2,
      y: rect.top - gridRect.top + rect.height / 2
    };
  }

  function drawLine(fromDot, toDot) {
    const start = getDotCenter(fromDot);
    const end = getDotCenter(toDot);
    ctx.beginPath();
    ctx.moveTo(start.x, start.y);
    ctx.lineTo(end.x, end.y);
    ctx.strokeStyle = "#007bff";
    ctx.lineWidth = 3;
    ctx.stroke();
  }

  function clearPattern() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    dots.forEach(d => d.classList.remove("active"));
    pattern = [];
    lastDot = null;
    senhaInput.value = "";
  }

  dots.forEach(dot => {
    dot.addEventListener("mousedown", () => {
      isDrawing = true;
      clearPattern();
      dot.classList.add("active");
      pattern.push(dot.dataset.id);
      lastDot = dot;
    });

    dot.addEventListener("mouseenter", () => {
      if (isDrawing && !dot.classList.contains("active")) {
        dot.classList.add("active");
        drawLine(lastDot, dot);
        pattern.push(dot.dataset.id);
        lastDot = dot;
      }
    });
  });

  document.addEventListener("mouseup", () => {
    if (isDrawing) {
      isDrawing = false;
      senhaInput.value = pattern.join("-");
    }
  });

  resetBtn.addEventListener("click", clearPattern);

});
</script>
