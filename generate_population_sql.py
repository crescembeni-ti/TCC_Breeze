import pandas as pd
import math

def generate_sql():
    try:
        # Carregar o Excel
        file_path = '/home/ubuntu/upload/CópiadeInventário(MapeamentodeEspécimes)(respostas).xlsx'
        df = pd.read_excel(file_path)
        
        # Identificar colunas
        cap_col = [c for c in df.columns if 'CAP' in c][0]
        lat_long_col = [c for c in df.columns if 'Latitude' in c][0]
        
        sql_commands = []
        PI = 3.141592653589793
        
        for index, row in df.iterrows():
            cap_raw = str(row[cap_col])
            coords_raw = str(row[lat_long_col])
            
            if cap_raw == 'nan' or coords_raw == 'nan':
                continue
            
            try:
                lat_str, lon_str = coords_raw.split(',')
                lat = float(lat_str.strip())
                lon = float(lon_str.strip())
            except:
                continue
            
            caps_list = [c.strip() for c in cap_raw.replace(' ', '').split(',') if c.strip()]
            update_assignments = []
            
            for i, cap_str in enumerate(caps_list):
                if i >= 20: break
                
                try:
                    cap_val = float(cap_str)
                    dap_val = round(cap_val / PI, 2)
                    
                    if i == 0:
                        # Primeiro valor vai para 'cap' e seu DAP para 'dap1'
                        update_assignments.append(f"cap = {cap_val}")
                        update_assignments.append(f"dap1 = {dap_val}")
                    else:
                        # Valores seguintes vão para 'cap2', 'cap3'... e 'dap2', 'dap3'...
                        col_idx = i + 1
                        update_assignments.append(f"cap{col_idx} = {cap_val}")
                        update_assignments.append(f"dap{col_idx} = {dap_val}")
                except ValueError:
                    continue
            
            if update_assignments:
                sql = f"UPDATE trees SET {', '.join(update_assignments)} WHERE ABS(latitude - {lat}) < 0.00001 AND ABS(longitude - {lon}) < 0.00001;"
                sql_commands.append(sql)
        
        with open('povoar_arvores.sql', 'w') as f:
            f.write("-- Script para povoar colunas cap, cap2..20 e dap1..20\n")
            f.write("-- Lógica: Primeiro valor em 'cap', demais em 'cap2', 'cap3'...\n\n")
            f.write('\n'.join(sql_commands))
            
        print(f"Sucesso! Gerado povoar_arvores.sql com {len(sql_commands)} comandos.")
        
    except Exception as e:
        print(f"Erro ao processar: {e}")

if __name__ == "__main__":
    generate_sql()
