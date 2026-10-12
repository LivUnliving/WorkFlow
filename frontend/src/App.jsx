import React, { useEffect, useState } from 'react';
const API='/WorkFlow/api/index.php';
const base='/WorkFlow/visao/';
const fmtData=s=>s ? s.slice(0,10).split('-').reverse().join('/') : '';
const moeda=v=>Number(v||0).toLocaleString('pt-BR',{style:'currency',currency:'BRL'});
const vazio={empresa_id:'',data_turno:'',horario_inicio:'',horario_fim:'',funcao:'',descricao:'',valor:'',funcionarios:[]};
export default function App(){
  const [dados,setDados]=useState(null),[erro,setErro]=useState(''),[tela,setTela]=useState('turnos');
  const [form,setForm]=useState(vazio),[editando,setEditando]=useState(null),[criando,setCriando]=useState(false);
  const [email,setEmail]=useState(''),[senha,setSenha]=useState(''),[carregando,setCarregando]=useState(false);
  async function consultar(){try{const r=await fetch(API,{credentials:'include',cache:'no-store'});const j=await r.json();setDados(j);}catch{setErro('Nao foi possivel conectar ao servidor PHP. Confira Apache e MySQL.');}}
  useEffect(()=>{consultar();},[]);
  async function acao(acao,campos={}){
    setErro('');setCarregando(true);
    try{
      const r=await fetch(API,{method:'POST',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify({acao,csrf:dados?.csrf,...campos})});
      const j=await r.json();if(!r.ok)throw Error(j.erro||'Falha na operacao');
      if(acao==='logout'){setDados({usuario:null});setTela('turnos');}
      else setDados(j);
      return true;
    }catch(e){setErro(e.message);return false;}
    finally{setCarregando(false);}
  }
  function campo(k,v){setForm(f=>({...f,[k]:v}));}
  function criar(){setForm({...vazio, empresa_id: String(dados.empresas?.[0]?.id||'')});setEditando(null);setCriando(true);setTela('editar');}
  function editar(t) {setForm({id: t.id,empresa_id: String(t.empresa_id),data_turno: t.data_turno,horario_inicio: t.horario_inicio?.slice(0, 5) || '',horario_fim: t.horario_fim?.slice(0, 5) || '',funcao: t.funcao,descricao: t.descricao,valor: t.valor,funcionarios: []});
  setEditando(t.id);
  setCriando(false);
  setTela('editar');
  }
  
  async function salvar(e) {e.preventDefault(); const ok = await acao(editando ? 'editar_turno' : 'criar_turno',form);
  if (ok) {setTela('turnos');setEditando(null);setCriando(false);}}
  const dono=dados?.usuario?.tipo==='dono';
  if (!dados) return <div className="espera">Abrindo WorkFlow...</div>;
  if (!dados.usuario) return <main className="login"><section className="painel"><div className="marca">WorkFlow</div><h1>Entrar na sua conta</h1>
    {erro && <p className="erro" role="alert">{erro}</p>}
    <form onSubmit={async e=>{e.preventDefault();await acao('login',{email,senha});}}><label>E-mail<input type="email" value={email} onChange={e=>setEmail(e.target.value)} required/></label>
    <label>Senha<input type="password" value={senha} onChange={e=>setSenha(e.target.value)} required/></label><button disabled={carregando} className="primario">Entrar</button></form>
    <p><a href={base+'EsqueciSenha.php'}>Esqueci minha senha</a></p>
    <p>Primeiro acesso? <a href={base+'CadastroEmpresa.php'}>Cadastrar empresa</a> ou <a href={base+'CadastroFreela.php'}>cadastrar freelancer</a>.</p>
    <small>Cadastros e recuperação de senha continuam nas páginas PHP durante a migração.</small></section></main>;
  const nots=dados.notificacoes||[];
  const voltar=()=>setTela('turnos');
  return <div className="app"><nav className="navegacao" aria-label="Navegacao principal">
    <strong className="logo">WorkFlow</strong>
    <button className={tela==='turnos'?'ativo':''} onClick={voltar} title="Turnos">▦ <span>Turnos</span></button>
    <button className={tela==='notificacoes'?'ativo':''} onClick={()=>setTela('notificacoes')} title="Notificações">♧ <span>Notificações</span>{dados.naoLidas>0&&<b className="contador">{dados.naoLidas}</b>}</button>
    <button className={tela==='config'?'ativo':''} onClick={()=>setTela('config')} title="Configurações">⚙ <span>Configurações</span></button>
    <button onClick={()=>{if(window.confirm('Deseja sair da conta?'))acao('logout');}} title="Sair">⇥ <span>Sair</span></button>
  </nav><main className="conteudo"><header className="topo"><div><small>{dono?'Painel do gerente':'Painel do freelancer'}</small><h1>Olá, {dados.usuario.nome}</h1></div></header>
  {erro&&<p className="erro" role="alert">{erro}</p>}
  {tela==='config'&&<section className="painel"><button className="link" onClick={voltar}>← Voltar</button><h2>Configurações</h2><p>Esta página será implementada futuramente.</p></section>}
  {tela==='notificacoes'&&<section><button className="link" onClick={voltar}>← Voltar</button><h2>Notificações</h2>{!nots.length&&<p>Nenhuma notificação por enquanto.</p>}
    {nots.map(n=><article className={'painel '+(!Number(n.lida)?'destaque':'')} key={n.id}><h3>{n.titulo}</h3><p>{n.mensagem}</p><small>{n.criada_em}</small><div className="linha"><button onClick={voltar}>Ver turnos</button>{!Number(n.lida)&&<button onClick={()=>acao('marcar_lida',{id:n.id})}>Marcar como lida</button>}</div></article>)}</section>}
  {tela==='turnos'&&dono&&<section><div className="linha"><h2>Turnos criados</h2><button className="primario" onClick={criar}>+ Novo turno</button></div>
    {!dados.turnos?.length&&<p>Nenhum turno cadastrado.</p>}
    <div className="cards">{(dados.turnos||[]).map(t=><article className="painel" key={t.id}><small>{t.empresa_nome}</small><h3>{t.funcao}</h3><p>{fmtData(t.data_turno)} às {t.horario_inicio.slice(0,5)}</p><p>{moeda(t.valor)}</p><p>{t.descricao}</p><p>Vagas: {t.vagas_disponiveis} de {t.vagas_necessarias} disponíveis</p>
      <div className="linha"><button onClick={()=>editar(t)}>Editar</button><button className="perigo" onClick={async()=>{if(window.confirm('Excluir turno e convites relacionados?')) await acao('excluir_turno',{id:t.id});}}>Excluir</button></div></article>)}</div>
    <p className="ajuda">Cadastro de lojas e aprovações da equipe: <a href={base+'PerfilDono.php'}>Meu perfil</a> e <a href={base+'Funcionarios.php'}>Funcionários</a>.</p></section>}
  {tela==='editar'&&dono&&<section className="painel"><button className="link" onClick={voltar}>← Voltar</button><h2>{criando?'Novo turno':'Editar turno'}</h2><form onSubmit={salvar}>
    {criando&&<label>Loja<select value={form.empresa_id} onChange={e=>{campo('empresa_id',e.target.value);setForm(f=>({...f,empresa_id:e.target.value,funcionarios:[]}));}} required>
      {(dados.empresas||[]).map(e=><option key={e.id} value={e.id}>{e.nome} — {e.codigo_loja}</option>)}</select></label>}
    <label>Data<input type="date" value={form.data_turno} onChange={e=>campo('data_turno',e.target.value)} required/></label>
    <label>Horário de entrada<input type="time" value={form.horario_inicio} onChange={e=>campo('horario_inicio',e.target.value)} required/></label>
    <label>Horário de saída<input type="time" value={form.horario_fim} onChange={e => campo('horario_fim', e.target.value)} required/></label>
    <label>Função<input type="text" maxLength="50" value={form.funcao} onChange={e=>campo('funcao',e.target.value)} required/></label>
    <label>Valor (R$)<input type="number" min="0" step="0.01" value={form.valor} onChange={e=>campo('valor',e.target.value)} required/></label>
    <label>Descrição<textarea maxLength="150" value={form.descricao} onChange={e=>campo('descricao',e.target.value)} required/></label>
    {criando?<fieldset><legend>Freelancers convidados</legend>{(dados.funcionarios||[]).filter(f=>String((dados.empresas||[]).find(e=>e.codigo_loja===f.codigo_loja)?.id)===String(form.empresa_id)).map(f=><label className="checagem" key={f.vinculo_id}>
      <input type="checkbox" checked={form.funcionarios.includes(Number(f.freelancer_id))} onChange={e=>campo('funcionarios',e.target.checked?[...form.funcionarios,Number(f.freelancer_id)]:form.funcionarios.filter(id=>id!==Number(f.freelancer_id)))}/>{f.nome}</label>)}
      {!(dados.funcionarios||[]).some(f=>String((dados.empresas||[]).find(e=>e.codigo_loja===f.codigo_loja)?.id)===String(form.empresa_id))&&<p>Aprove freelancers para esta loja antes de criar o turno.</p>}
    </fieldset>:<p>Os convites existentes não são alterados nesta edição.</p>}
    <button className="primario" disabled={carregando}>Salvar</button></form></section>}
  {tela==='turnos'&&!dono&&<section><h2>Convites de turnos</h2><div className="cards">
    {!(dados.pendentes||[]).length&&<p>Você não tem convites pendentes.</p>}
    {(dados.pendentes||[]).map(t=><article className="painel" key={t.id}><small>{t.empresa_nome}</small><h3>{t.funcao}</h3><p>{fmtData(t.data_turno)} às {t.horario_inicio.slice(0,5)}</p><p>{moeda(t.valor)}</p><p>{t.descricao}</p><div className="linha"><button className="primario" onClick={()=>acao('responder_turno',{id:t.id,status:'Confirmado'})}>Aceitar</button><button className="perigo" onClick={()=>acao('responder_turno',{id:t.id,status:'Recusado'})}>Recusar</button></div></article>)}
    </div><h2>Turnos respondidos</h2><div className="cards">{(dados.respondidos||[]).map((t,i)=><article className="painel" key={i}><h3>{t.funcao}</h3><p>{t.empresa_nome} · {fmtData(t.data_turno)}</p><strong>{t.status}</strong></article>)}</div>
    <p className="ajuda">Para solicitar vínculo com lojas, use <a href={base+'PerfilFreela.php'}>Meu perfil</a>.</p>
  </section>}
  </main></div>;
}
