<script lang="ts">
  import { onMount } from 'svelte';
  import { goto } from '$app/navigation';
  import { resolve } from '$app/paths';
  import Button from '../../../../components/Button.svelte';
  import Alert from '../../../../components/Alert.svelte';
  import Modal from '../../../../components/Modal.svelte';
  import DataTable from '../../../../components/DataTable.svelte';
  import type { DataAction, DataColumn, SortOrder } from '../../../../components/data-table';
  import type { PageSize } from '../../../../components/pagination';
  import { ApiError } from '../../../../api/client';
  import { boApi } from '../../../../api/session';
  import { uiLanguage } from '../../../../api/locales';
  import {
    roleCopy, validNewRoleCode, permissionGroups,
    type BoRole, type BoRolesResponse, type BoPermission, type BoPermissionCatalog
  } from '../../presentation';

  let roles = $state<BoRole[]>([]);
  let catalogue = $state<BoPermission[]>([]);
  let canManage = $state(false);
  let editing = $state<BoRole | null>(null);
  let showForm = $state(false);
  let code = $state('ROLE_');
  let label = $state('');
  let chosen = $state<string[]>([]);
  let error = $state('');
  let formError = $state('');
  let notice = $state('');
  let loading = $state(false);
  let saving = $state(false);
  let deleting = $state<number | null>(null);
  let search = $state('');
  let sortKey = $state<string | null>('label');
  let sortOrder = $state<SortOrder>('asc');
  let page = $state(1);
  let limit = $state<PageSize>(20);

  const language = $derived($uiLanguage ?? (typeof navigator === 'undefined' ? 'en' : navigator.language));
  const t = $derived(roleCopy(language));
  const groups = $derived(permissionGroups(catalogue));
  const filtered = $derived([...roles]
    .filter((role) => `${role.code} ${role.label}`.toLocaleLowerCase().includes(search.toLocaleLowerCase()))
    .sort((a, b) => {
      const key = sortKey === 'code' ? 'code' : 'label';
      return a[key].localeCompare(b[key], language, { sensitivity: 'base' }) *
        (sortOrder === 'asc' ? 1 : -1);
    })
  );
  const visible = $derived(filtered.slice((page - 1) * limit, page * limit));
  const columns = $derived<DataColumn<BoRole>[]>([
    { key: 'label', label: t.label, sortable: true },
    { key: 'code', label: t.code, sortable: true },
    { key: 'native', label: t.kind, format: (v) => v ? t.native : t.custom },
    { key: 'permissions', label: t.access,
      format: (_, row) => row.native && row.code === 'ROLE_DEV' ? '∞'
        : row.native && row.code === 'ROLE_SUPER_ADMIN' ? '∞'
        : String(row.permissions.length) }
  ]);
  const actions = $derived<DataAction<BoRole>[]>([
    {
      key: 'edit', label: (row) => row.native ? t.protected : t.edit,
      disabled: (row) => row.native,
      onClick: (row) => open(row)
    },
    {
      key: 'delete', variant: 'danger', label: t.delete,
      disabled: (row) => row.native || deleting !== null,
      onClick: (row) => { void remove(row); }
    }
  ]);

  function explain(cause: unknown): string {
    if (cause instanceof ApiError) {
      if (cause.status === 401) {
        void goto(resolve('/login'), { replaceState: true });
        return t.session;
      }
      if (cause.status === 403) return t.forbidden;
      if (cause.status === 409) return t.conflict;
      if (cause.status === 400) return t.invalid;
    }
    return t.failure;
  }

  async function load(): Promise<void> {
    loading = true;
    error = '';
    try {
      const response = await boApi<BoRolesResponse>('/apibo/roles');
      roles = Array.isArray(response.data) ? response.data : [];
      try {
        const available = await boApi<BoPermissionCatalog>('/apibo/acls');
        catalogue = Array.isArray(available.data) ? available.data : [];
        canManage = available.can_manage === true;
      } catch {
        catalogue = [];
        canManage = false;
      }
    } catch (cause) {
      error = explain(cause);
    } finally {
      loading = false;
    }
  }

  onMount(() => { void load(); });

  function open(role?: BoRole): void {
    if (role?.native) return;
    editing = role ?? null;
    code = role?.code ?? 'ROLE_';
    label = role?.label ?? '';
    chosen = [...(role?.permissions ?? [])];
    formError = '';
    showForm = true;
  }

  function toggle(code: string): void {
    chosen = chosen.includes(code) ? chosen.filter((item) => item !== code) : [...chosen, code];
  }

  async function save(event: SubmitEvent): Promise<void> {
    event.preventDefault();
    if (saving) return;
    if (!editing && !validNewRoleCode(code.trim())) { formError = t.invalidCode; return; }
    saving = true;
    formError = '';
    notice = '';
    const body: Record<string, unknown> = editing ? { label: label.trim() }
      : { code: code.trim(), label: label.trim() };
    if (canManage) body.permissions = chosen;
    try {
      if (editing) {
        await boApi<unknown>(`/apibo/roles/${editing.id}`, {
          method: 'PATCH', body: JSON.stringify(body)
        });
      } else {
        await boApi<unknown>('/apibo/roles', {
          method: 'POST', body: JSON.stringify(body)
        });
      }
      notice = editing ? t.updated : t.created;
      showForm = false;
      await load();
    } catch (cause) {
      formError = explain(cause);
    } finally {
      saving = false;
    }
  }

  async function remove(role: BoRole): Promise<void> {
    if (role.native || deleting !== null || !window.confirm(t.confirmDelete)) return;
    deleting = role.id;
    error = '';
    notice = '';
    try {
      await boApi<unknown>(`/apibo/roles/${role.id}`, { method: 'DELETE' });
      notice = t.deleted;
      await load();
    } catch (cause) {
      error = explain(cause);
    } finally {
      deleting = null;
    }
  }
</script>

<svelte:head><title>{t.title} | SymSvel BO</title></svelte:head>
<div class="roles-page">
  <header class="page-heading">
    <div><h1>{t.heading}</h1><p>{t.intro}</p></div>
    <Button type="button" onclick={() => open()}>{t.create}</Button>
  </header>
  {#if error}<Alert kind="error">{error}</Alert>{/if}
  {#if notice}<Alert kind="success">{notice}</Alert>{/if}
  <section class="panel">
    <div class="section-heading">
      <h2>{t.title}</h2>
      <Button type="button" variant="secondary" onclick={load} disabled={loading}>{t.refresh}</Button>
    </div>
    <DataTable rows={visible} {columns} {actions} {loading} emptyMessage={t.none}
      filters={[{ key: 'search', label: t.search, type: 'search', placeholder: t.searchHint }]}
      filterValues={{ search }}
      onFilterChange={(_, value) => { search = value; page = 1; }}
      {sortKey} {sortOrder}
      onSortChange={(key, order) => { sortKey = key; sortOrder = order; page = 1; }}
      pagination={{
        page, limit, total: filtered.length,
        onPageChange: (value) => page = value,
        onLimitChange: (value) => { limit = value; page = 1; }
      }}
    />
  </section>

  <Modal open={showForm} title={editing ? t.edit : t.create} onClose={() => showForm = false}>
    {#if formError}<Alert kind="error">{formError}</Alert>{/if}
    <form class="role-form" onsubmit={save}>
      <label for="role-code">{t.code}</label>
      <input id="role-code" bind:value={code} disabled={editing !== null} maxlength="100" required />
      {#if !editing}<small>{t.codeRule}</small>{/if}
      <label for="role-label">{t.label}</label>
      <input id="role-label" bind:value={label} maxlength="150" required />
      {#if canManage && catalogue.length > 0}
        <fieldset>
          <legend>{t.permissions}</legend>
          {#each Object.entries(groups) as [resource, rights] (resource)}
            <section class="permission-group">
              <strong>{resource}</strong>
              {#each rights as permission (permission.code)}
                <label class="permission-choice">
                  <input type="checkbox" checked={chosen.includes(permission.code)}
                    onchange={() => toggle(permission.code)} />
                  <span>{permission.label}<small>{permission.code}{permission.technical ? ` · ${t.technical}` : ''}</small></span>
                </label>
              {/each}
            </section>
          {/each}
        </fieldset>
      {:else}
        <small>{t.noManage}</small>
      {/if}
      <div class="role-actions">
        <Button type="button" variant="secondary" onclick={() => showForm = false}>{t.cancel}</Button>
        <Button type="submit" variant="success" disabled={saving}>{saving ? t.saving : t.save}</Button>
      </div>
    </form>
  </Modal>
</div>

<style>
  .roles-page { max-width: 1200px; margin: auto; display: grid; gap: 20px; }
  .page-heading, .section-heading { display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap; }
  .page-heading h1, h2 { margin: 0; }
  .page-heading p, small { color: var(--bo-muted); }
  .panel { padding: 24px; background: var(--bo-surface); color: var(--bo-text); border: 1px solid var(--bo-border); border-radius: 14px; min-width: 0; }
  .section-heading { margin-bottom: 16px; }
  .role-form { display: grid; gap: 10px; }
  .role-form > label { font-weight: 650; }
  .role-form > input { padding: 11px; border: 1px solid var(--bo-border); border-radius: 8px; font: inherit; background: var(--bo-input); color: var(--bo-text); min-width: 0; }
  .role-form > input:focus-visible { outline: 2px solid var(--bo-focus); outline-offset: 2px; }
  fieldset { border: 1px solid var(--bo-border); border-radius: 10px; max-height: 350px; overflow: auto; margin-top: 14px; }
  legend { font-weight: 700; }
  .permission-group { display: grid; gap: 8px; margin: 12px 0; }
  .permission-choice { display: flex; align-items: flex-start; gap: 8px; font-size: .9rem; }
  .permission-choice input { margin-top: 4px; accent-color: var(--bo-primary); }
  .permission-choice small { display: block; margin-top: 4px; font-size: .72rem; }
  .roles-page { color: var(--bo-text); }
  .role-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 16px; }
</style>
