<h3 onclick="cuenta.chgsec(this)">4. Intereses y preferencias</h3>
<fieldset class="nodisplay">
    <div class="alert-cuenta cuenta-5">
    </div>
    <div class="field">
        <label for="mis_intereses">Mis intereses</label>
        <div class="input-fake">
            <textarea class="cuenta-save-5" name="intereses" id="mis_intereses">{$psPerfil.p_intereses}</textarea>
        </div>
    </div>
    <div class="field">
        <label for="hobbies">Hobbies</label>
        <div class="input-fake">
            <textarea class="cuenta-save-5" name="hobbies" id="hobbies">{$psPerfil.p_hobbies}</textarea>
        </div>
    </div>
    <div class="field">
        <label for="mis_heroes_son">Mis h&eacute;roes favoritos son</label>
        <div class="input-fake">
            <textarea class="cuenta-save-5" name="heroes" id="mis_heroes_son">{$psPerfil.p_heroes}</textarea>
        </div>
    </div>
    <div class="field">
        <label for="musica_favorita">M&uacute;sica favorita</label>
        <div class="input-fake">
            <textarea class="cuenta-save-5" name="musica" id="musica_favorita">{$psPerfil.p_musica}</textarea>
        </div>
    </div>
    <div class="field">
        <label for="deportes_y_equipos_favoritos">Deportes y equipos favoritos</label>
        <div class="input-fake">
            <textarea class="cuenta-save-5" name="deportes" id="deportes_y_equipos_favoritos">{$psPerfil.p_deportes}</textarea>
        </div>
    </div>
    <div class="field">
        <label for="libros_favoritos">Libros favoritos</label>
        <div class="input-fake">
            <textarea class="cuenta-save-5" name="libros" id="libros_favoritos">{$psPerfil.p_libros}</textarea>
        </div>
    </div>
    <div class="field">
        <label for="peliculas_favoritas">Pel&iacute;culas favoritas</label>
        <div class="input-fake">
            <textarea class="cuenta-save-5" name="peliculas" id="peliculas_favoritas">{$psPerfil.p_peliculas}</textarea>
        </div>
    </div>
    <div class="field">
        <label for="series_tv_favoritas">Series de TV favoritas:</label>
        <div class="input-fake">
            <textarea class="cuenta-save-5" name="tv" id="series_tv_favoritas">{$psPerfil.p_tv}</textarea>
        </div>
    </div>
    <div class="field">
        <label for="comida_favorita">Comida favorita</label>
        <div class="input-fake">
            <textarea class="cuenta-save-5" name="comida" id="comida_favorita">{$psPerfil.p_comida}</textarea>
        </div>
    </div> 
    <div class="buttons">
        <input type="button" value="Guardar" onclick="cuenta.save(5)" class="btn btn-success">
    </div>
</fieldset>